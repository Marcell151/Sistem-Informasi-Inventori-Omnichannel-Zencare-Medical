<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/order_helper.php';

echo "=======================================================\n";
echo "   PENGUJIAN FLOW REQUEST BATAL PELANGGAN & APPROVAL\n";
echo "=======================================================\n\n";

$pdo->beginTransaction();

try {
    // 1. Ambil 1 produk variasi obat & stok batch
    $var = $pdo->query("
        SELECT pv.id, pv.rasio_konversi, st.stok, sb.id AS id_batch, sb.stok_sisa, sb.no_batch
        FROM produk_variasi pv
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        JOIN stok_toko st ON pv.id = st.id_variasi
        JOIN stok_batch sb ON pv.id = sb.id_variasi
        WHERE pi.kategori = 'Obat' AND sb.stok_sisa >= 10
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    if (!$var) {
        throw new Exception("Data variasi/batch obat tidak ditemukan.");
    }

    $idVar = $var['id'];
    $idBatch = $var['id_batch'];
    $stokAwalToko = intval($var['stok']);
    $stokAwalBatch = intval($var['stok_sisa']);
    $rasio = max(1, intval($var['rasio_konversi']));
    $qtyOrder = 2; // 2 Box
    $qtyPotong = $qtyOrder * $rasio;

    // Simulasi buat pesanan langsung Diproses
    $invoice = 'TEST-REQ-' . time();
    $pdo->prepare("
        INSERT INTO penjualan (no_invoice, id_user, tipe_transaksi, total_harga, status_pesanan, kurir, layanan, metode_pengambilan, created_at)
        VALUES (?, 1, 'ecommerce', 100000, 'Diproses', 'jne', 'REG', 'Kurir', NOW())
    ")->execute([$invoice]);
    $idOrder = $pdo->lastInsertId();

    $pdo->prepare("
        INSERT INTO detail_penjualan (id_penjualan, id_variasi, qty, harga_satuan, catatan_logistik)
        VALUES (?, ?, ?, 50000, ?)
    ")->execute([$idOrder, $idVar, $qtyOrder, "{$var['no_batch']} ({$qtyPotong}x)"]);
    $idDetail = $pdo->lastInsertId();

    // Potong stok dan alokasikan
    $pdo->prepare("UPDATE stok_toko SET stok = stok - ? WHERE id_variasi = ?")->execute([$qtyPotong, $idVar]);
    $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa - ? WHERE id = ?")->execute([$qtyPotong, $idBatch]);
    $pdo->prepare("INSERT INTO pesanan_batch_alokasi (id_penjualan, id_detail_penjualan, id_batch, qty_satuan_kecil) VALUES (?, ?, ?, ?)")
        ->execute([$idOrder, $idDetail, $idBatch, $qtyPotong]);

    // Pelanggan ajukan pembatalan
    $alasanPengajuan = "Ingin mengubah alamat pengiriman";
    $pdo->prepare("UPDATE penjualan SET request_batal = 1, request_batal_alasan = ?, request_batal_waktu = NOW() WHERE id = ?")
        ->execute([$alasanPengajuan, $idOrder]);

    $checkReq = $pdo->query("SELECT request_batal, request_batal_alasan, status_pesanan FROM penjualan WHERE id = $idOrder")->fetch();
    if ($checkReq['request_batal'] == 1 && $checkReq['status_pesanan'] === 'Diproses') {
        echo " [PASS] Test 1: Pelanggan berhasil mengajukan pembatalan saat status Diproses.\n";
    } else {
        throw new Exception("Test 1 Gagal: request_batal tidak sesuai.");
    }

    // Admin Menyetujui Pembatalan
    $adminNama = "Admin Super";
    $alasanFinal = "Permohonan Pelanggan: " . $checkReq['request_batal_alasan'];
    $pelaku = "Admin ($adminNama) - Persetujuan Pelanggan";
    batalkanSatuPesananInternal($pdo, $idOrder, $alasanFinal, $pelaku);

    $checkFinal = $pdo->query("SELECT status_pesanan, alasan_batal, dibatalkan_oleh, request_batal FROM penjualan WHERE id = $idOrder")->fetch();
    if ($checkFinal['status_pesanan'] === 'Dibatalkan' && $checkFinal['request_batal'] == 0 && strpos($checkFinal['alasan_batal'], 'Ingin mengubah alamat') !== false) {
        echo " [PASS] Test 2: Admin berhasil menyetujui pembatalan, request_batal di-reset, status menjadi Dibatalkan.\n";
    } else {
        throw new Exception("Test 2 Gagal: Status/alasan tidak sesuai.");
    }

    // Cek restorasi stok
    $stokAkhirToko = intval($pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVar")->fetchColumn());
    $stokAkhirBatch = intval($pdo->query("SELECT stok_sisa FROM stok_batch WHERE id = $idBatch")->fetchColumn());

    if ($stokAkhirToko === $stokAwalToko && $stokAkhirBatch === $stokAwalBatch) {
        echo " [PASS] Test 3: Restorasi stok toko dan stok batch FEFO 100% pulih sempurna.\n";
    } else {
        throw new Exception("Test 3 Gagal: Stok tidak kembali ke saldo awal! Toko: $stokAkhirToko (Awal: $stokAwalToko), Batch: $stokAkhirBatch (Awal: $stokAwalBatch)");
    }

    echo "\n=======================================================\n";
    echo " HASIL PENGUJIAN: SEMUA SKENARIO REQUEST BATAL PASS!\n";
    echo "=======================================================\n";

} catch (Exception $e) {
    echo " [FAIL] Terjadi kesalahan: " . $e->getMessage() . "\n";
} finally {
    $pdo->rollBack();
    echo "\n [INFO] Transaksi database di-rollback (database tetap bersih).\n";
}
