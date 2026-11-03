<?php
require __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/order_helper.php';

echo "=======================================================\n";
echo "   PENGUJIAN VALIDASI FITUR ALASAN BATAL (TAHAP 1)\n";
echo "=======================================================\n\n";

$pdo->beginTransaction();

try {
    // 1. Ambil 1 produk aktif dengan batch untuk pengujian
    $p = $pdo->query("
        SELECT pv.id AS id_variasi, pv.harga_jual_besar, pv.rasio_konversi, sb.id AS id_batch, sb.stok_sisa, st.stok AS stok_toko
        FROM produk_variasi pv
        JOIN stok_batch sb ON sb.id_variasi = pv.id
        JOIN stok_toko st ON st.id_variasi = pv.id
        WHERE sb.stok_sisa >= 5 AND st.stok >= 5
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    if (!$p) throw new Exception("Data produk untuk testing tidak ditemukan.");

    $idVar = $p['id_variasi'];
    $idBatch = $p['id_batch'];
    $rasio = max(1, intval($p['rasio_konversi']));
    $stokAwalToko = intval($p['stok_toko']);
    $stokAwalBatch = intval($p['stok_sisa']);

    // --- TEST 1: SIMULASI AUTO-CANCEL OLEH TIMER ---
    // Buat pesanan dummy berstatus 'Menunggu Pembayaran'
    $inv1 = 'TEST-CANCEL-TIMER-' . rand(100, 999);
    $pdo->prepare("
        INSERT INTO penjualan (no_invoice, id_user, tipe_transaksi, status_pesanan, total_harga, created_at)
        VALUES (?, 1, 'ecommerce', 'Menunggu Pembayaran', 100000, NOW() - INTERVAL 150 MINUTE)
    ")->execute([$inv1]);
    $orderId1 = $pdo->lastInsertId();

    $pdo->prepare("
        INSERT INTO detail_penjualan (id_penjualan, id_variasi, qty, harga_satuan)
        VALUES (?, ?, 1, 100000)
    ")->execute([$orderId1, $idVar]);
    $detId1 = $pdo->lastInsertId();

    // Potong stok (simulasi reservasi checkout: 1 box = rasio pcs)
    $pdo->prepare("UPDATE stok_toko SET stok = stok - ? WHERE id_variasi = ?")->execute([$rasio, $idVar]);
    $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa - ? WHERE id = ?")->execute([$rasio, $idBatch]);
    $pdo->prepare("INSERT INTO pesanan_batch_alokasi (id_penjualan, id_detail_penjualan, id_batch, qty_satuan_kecil) VALUES (?, ?, ?, ?)")
        ->execute([$orderId1, $detId1, $idBatch, $rasio]);

    // Jalankan auto-cancel timer
    $cancelledCount = batalkanPesananKadaluarsa($pdo);
    
    // Cek hasil pesanan 1
    $q1 = $pdo->prepare("SELECT status_pesanan, alasan_batal, dibatalkan_oleh, waktu_batal FROM penjualan WHERE id = ?");
    $q1->execute([$orderId1]);
    $res1 = $q1->fetch(PDO::FETCH_ASSOC);

    assert($res1['status_pesanan'] === 'Dibatalkan', "Status bukan Dibatalkan");
    assert($res1['alasan_batal'] === 'Waktu pembayaran habis (Expired)', "Alasan batal timer salah");
    assert($res1['dibatalkan_oleh'] === 'Sistem (Timer)', "Pelaku batal timer salah");
    assert(!empty($res1['waktu_batal']), "Waktu batal kosong");
    echo " [PASS] Test 1: Auto-Cancel Timer mencatat alasan 'Waktu pembayaran habis (Expired)' & pelaku 'Sistem (Timer)'.\n";

    // --- TEST 2: SIMULASI PEMBATALAN OLEH PELANGGAN ---
    $inv2 = 'TEST-CANCEL-CUST-' . rand(100, 999);
    $pdo->prepare("
        INSERT INTO penjualan (no_invoice, id_user, tipe_transaksi, status_pesanan, total_harga, created_at)
        VALUES (?, 1, 'ecommerce', 'Menunggu Pembayaran', 50000, NOW())
    ")->execute([$inv2]);
    $orderId2 = $pdo->lastInsertId();

    $pdo->prepare("
        INSERT INTO detail_penjualan (id_penjualan, id_variasi, qty, harga_satuan)
        VALUES (?, ?, 1, 50000)
    ")->execute([$orderId2, $idVar]);
    $detId2 = $pdo->lastInsertId();

    // Potong stok (1 box = rasio pcs)
    $pdo->prepare("UPDATE stok_toko SET stok = stok - ? WHERE id_variasi = ?")->execute([$rasio, $idVar]);
    $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa - ? WHERE id = ?")->execute([$rasio, $idBatch]);
    $pdo->prepare("INSERT INTO pesanan_batch_alokasi (id_penjualan, id_detail_penjualan, id_batch, qty_satuan_kecil) VALUES (?, ?, ?, ?)")
        ->execute([$orderId2, $detId2, $idBatch, $rasio]);

    // Pelanggan membatalkan dengan alasan
    $alasanCust = 'Ingin mengubah rincian pesanan (Ganti varian)';
    batalkanSatuPesananInternal($pdo, $orderId2, $alasanCust, 'Pelanggan');

    $q2 = $pdo->prepare("SELECT status_pesanan, alasan_batal, dibatalkan_oleh, waktu_batal FROM penjualan WHERE id = ?");
    $q2->execute([$orderId2]);
    $res2 = $q2->fetch(PDO::FETCH_ASSOC);

    assert($res2['status_pesanan'] === 'Dibatalkan', "Status bukan Dibatalkan");
    assert($res2['alasan_batal'] === $alasanCust, "Alasan batal pelanggan salah");
    assert($res2['dibatalkan_oleh'] === 'Pelanggan', "Pelaku batal pelanggan salah");
    echo " [PASS] Test 2: Pembatalan Pelanggan mencatat alasan kustom & pelaku 'Pelanggan'.\n";

    // --- TEST 3: RESTORASI STOK SEMPURNA ---
    $stokAkhirToko = intval($pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVar")->fetchColumn());
    $stokAkhirBatch = intval($pdo->query("SELECT stok_sisa FROM stok_batch WHERE id = $idBatch")->fetchColumn());

    assert($stokAkhirToko === $stokAwalToko, "Stok toko tidak kembali ke awal ($stokAkhirToko != $stokAwalToko)");
    assert($stokAkhirBatch === $stokAwalBatch, "Stok batch tidak kembali ke awal ($stokAkhirBatch != $stokAwalBatch)");
    echo " [PASS] Test 3: Restorasi stok toko dan stok batch FEFO 100% kembali ke saldo awal.\n";

    echo "\n=======================================================\n";
    echo " HASIL PENGUJIAN: SEMUA TEST TAHAP 1 BERHASIL (PASS)\n";
    echo "=======================================================\n";

} catch (Exception $e) {
    echo " [FAIL] Pengujian Gagal: " . $e->getMessage() . "\n";
} finally {
    $pdo->rollBack();
    echo "\n [INFO] Transaksi database di-rollback (database tetap bersih).\n";
}
