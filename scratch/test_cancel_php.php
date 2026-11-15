<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/order_helper.php';

echo "=== VERIFYING REAL CANCELLATION FLOW & RESERVED STOCK RESTORATION ===\n";

$pdo->beginTransaction();
try {
    // Pick first available medicine variasi
    $v = $pdo->query("
        SELECT pv.id, pv.id_produk_induk, pv.rasio_konversi, st.stok, sb.id AS id_batch, sb.stok_sisa, sb.no_batch
        FROM produk_variasi pv
        JOIN stok_toko st ON pv.id = st.id_variasi
        JOIN stok_batch sb ON pv.id = sb.id_variasi
        WHERE st.stok > 20 AND sb.stok_sisa > 20
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    if (!$v) {
        throw new Exception("No suitable test product found.");
    }

    $idVar = $v['id'];
    $idBatch = $v['id_batch'];
    $rasio = max(1, intval($v['rasio_konversi'] ?: 1));
    $stokTokoAwal = intval($v['stok']);
    $stokBatchAwal = intval($v['stok_sisa']);
    echo "Test Variasi ID: $idVar (Rasio: $rasio, Batch: {$v['no_batch']})\n";
    echo "Stok Toko Awal: $stokTokoAwal, Stok Batch Awal: $stokBatchAwal\n";

    // 1. Create dummy unpaid order
    $noInvTest = 'INV-TEST-DEMO-' . time();
    $stmtIns = $pdo->prepare("
        INSERT INTO penjualan (no_invoice, id_user, tipe_transaksi, metode_pengambilan, status_pesanan, total_harga, created_at)
        VALUES (?, 1, 'ecommerce', 'Delivery', 'Menunggu Pembayaran', 50000, NOW())
    ");
    $stmtIns->execute([$noInvTest]);
    $idPenjualan = $pdo->lastInsertId();

    // 2. Add detail_penjualan (qty = 2 box)
    $qtyBoxTest = 2;
    $qtyPotongTest = $qtyBoxTest * $rasio;
    $stmtDet = $pdo->prepare("
        INSERT INTO detail_penjualan (id_penjualan, id_variasi, qty, harga_satuan, catatan_logistik)
        VALUES (?, ?, ?, 25000, ?)
    ");
    $stmtDet->execute([$idPenjualan, $idVar, $qtyBoxTest, "{$v['no_batch']} ($qtyPotongTest)"]);
    $idDetail = $pdo->lastInsertId();

    // 3. Reserve stock (simulate checkout: cut stok_toko & stok_batch & insert pesanan_batch_alokasi)
    $pdo->prepare("UPDATE stok_toko SET stok = stok - ? WHERE id_variasi = ?")->execute([$qtyPotongTest, $idVar]);
    $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa - ? WHERE id = ?")->execute([$qtyPotongTest, $idBatch]);
    $pdo->prepare("INSERT INTO pesanan_batch_alokasi (id_penjualan, id_detail_penjualan, id_batch, qty_satuan_kecil) VALUES (?, ?, ?, ?)")
        ->execute([$idPenjualan, $idDetail, $idBatch, $qtyPotongTest]);

    // Check intermediate stock
    $stokTokoMid = intval($pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVar")->fetchColumn());
    $stokBatchMid = intval($pdo->query("SELECT stok_sisa FROM stok_batch WHERE id = $idBatch")->fetchColumn());
    echo "Setelah Checkout (Reserved): Stok Toko = $stokTokoMid (-$qtyPotongTest), Stok Batch = $stokBatchMid (-$qtyPotongTest)\n";
    if ($stokTokoMid !== $stokTokoAwal - $qtyPotongTest) throw new Exception("Stok Toko deduction failed");
    if ($stokBatchMid !== $stokBatchAwal - $qtyPotongTest) throw new Exception("Stok Batch deduction failed");

    // 4. CANCEL the order using batalkanSatuPesananInternal
    $alasanDemo = "Pelanggan membatalkan pesanan (Uji Coba)";
    $pelakuDemo = "Pelanggan";
    $success = batalkanSatuPesananInternal($pdo, $idPenjualan, $alasanDemo, $pelakuDemo);
    echo "batalkanSatuPesananInternal returned: " . ($success ? "TRUE" : "FALSE") . "\n";

    // 5. Check restored stock
    $stokTokoAkhir = intval($pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVar")->fetchColumn());
    $stokBatchAkhir = intval($pdo->query("SELECT stok_sisa FROM stok_batch WHERE id = $idBatch")->fetchColumn());
    echo "Setelah Dibatalkan (Restored): Stok Toko = $stokTokoAkhir (+$qtyPotongTest), Stok Batch = $stokBatchAkhir (+$qtyPotongTest)\n";
    if ($stokTokoAkhir !== $stokTokoAwal) throw new Exception("Stok Toko NOT fully restored!");
    if ($stokBatchAkhir !== $stokBatchAwal) throw new Exception("Stok Batch NOT fully restored!");

    // 6. Check order status in DB
    $resOrder = $pdo->query("SELECT status_pesanan, alasan_batal, dibatalkan_oleh, waktu_batal FROM penjualan WHERE id = $idPenjualan")->fetch(PDO::FETCH_ASSOC);
    echo "Order Record: Status = '{$resOrder['status_pesanan']}', Alasan = '{$resOrder['alasan_batal']}', Oleh = '{$resOrder['dibatalkan_oleh']}', Waktu = '{$resOrder['waktu_batal']}'\n";
    if ($resOrder['status_pesanan'] !== 'Dibatalkan') throw new Exception("Status not Dibatalkan");
    if ($resOrder['dibatalkan_oleh'] !== 'Pelanggan') throw new Exception("Oleh not Pelanggan");
    if (empty($resOrder['waktu_batal'])) throw new Exception("Waktu batal empty");

    // 7. Check alokasi deleted
    $allocCheck = intval($pdo->query("SELECT COUNT(*) FROM pesanan_batch_alokasi WHERE id_penjualan = $idPenjualan")->fetchColumn());
    echo "Alokasi sisa untuk pesanan: $allocCheck (0 = Sukses Terhapus)\n";
    if ($allocCheck !== 0) throw new Exception("Alokasi was not deleted");

    echo "\n>>> HASIL PENGUJIAN: FUNGSI PEMBATALAN & PELEPASAN STOK BERFUNGSI 100% VALID & PRESISI! <<<\n";
    $pdo->rollBack();
    echo "Rollback selesai, data database tetap bersih.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
