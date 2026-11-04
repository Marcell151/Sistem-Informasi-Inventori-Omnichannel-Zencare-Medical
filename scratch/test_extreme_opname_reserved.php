<?php
require_once __DIR__ . '/../config/koneksi.php';

echo "=== PENGUJIAN KASUS EKSTREM OPNAME: STOK_SISA = 0 & RESERVED PENUH ===\n\n";

$pdo->beginTransaction();
try {
    // 1. Pilih variasi obat aktif, misal ID 1 (Amoxicillin)
    $idVar = 1;

    // 2. Buat batch uji dengan stok_sisa = 0
    $pdo->prepare("
        INSERT INTO stok_batch (id_variasi, no_batch, tgl_exp, stok_sisa, created_at)
        VALUES (?, 'BATCH-ZERO-TEST', DATE_ADD(CURDATE(), INTERVAL 365 DAY), 0, NOW())
    ")->execute([$idVar]);
    $testBatchId = $pdo->lastInsertId();

    // 3. Buat pesanan status 'Diproses'
    $pdo->prepare("
        INSERT INTO penjualan (no_invoice, id_user, tipe_transaksi, status_pesanan, total_harga, created_at)
        VALUES ('INV-TEST-OPNAME-01', 2, 'ecommerce', 'Diproses', 25000, NOW())
    ")->execute();
    $testPenjualanId = $pdo->lastInsertId();

    $pdo->prepare("
        INSERT INTO detail_penjualan (id_penjualan, id_variasi, qty, harga_satuan)
        VALUES (?, ?, 1, 25000)
    ")->execute([$testPenjualanId, $idVar]);
    $testDetailId = $pdo->lastInsertId();

    // 4. Alokasikan 10 strip (1 Box) ke pesanan_batch_alokasi untuk batch nol ini
    $pdo->prepare("
        INSERT INTO pesanan_batch_alokasi (id_penjualan, id_detail_penjualan, id_batch, qty_satuan_kecil, created_at)
        VALUES (?, ?, ?, 10, NOW())
    ")->execute([$testPenjualanId, $testDetailId, $testBatchId]);

    echo "1. Data Uji Berhasil Dibuat:\n";
    echo "   - ID Batch: $testBatchId (BATCH-ZERO-TEST)\n";
    echo "   - stok_sisa: 0\n";
    echo "   - Pesanan: INV-TEST-OPNAME-01 (Status: Diproses)\n";
    echo "   - Alokasi Reserved di pesanan_batch_alokasi: 10 Strip\n\n";

    // 5. Eksekusi Query Snapshot Opname (Query Asli dari inventori/stok_opname.php)
    $sqlSnapshot = "
        SELECT sb.id AS id_batch, sb.id_variasi, sb.no_batch, sb.stok_sisa,
               COALESCE((
                   SELECT SUM(pba.qty_satuan_kecil) 
                   FROM pesanan_batch_alokasi pba 
                   JOIN penjualan p ON pba.id_penjualan = p.id 
                   WHERE pba.id_batch = sb.id 
                     AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
               ), 0) AS stok_reserved_kecil,
               pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, pi.nama_produk, pv.nama_variasi
        FROM stok_batch sb
        JOIN produk_variasi pv ON sb.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        WHERE pv.is_active = 1 
          AND (sb.stok_sisa + COALESCE((
                   SELECT SUM(pba.qty_satuan_kecil) 
                   FROM pesanan_batch_alokasi pba 
                   JOIN penjualan p ON pba.id_penjualan = p.id 
                   WHERE pba.id_batch = sb.id 
                     AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
               ), 0)) > 0
          AND sb.id = ?
    ";
    $stmtTest = $pdo->prepare($sqlSnapshot);
    $stmtTest->execute([$testBatchId]);
    $result = $stmtTest->fetch(PDO::FETCH_ASSOC);

    echo "2. Hasil Evaluasi Query Snapshot Opname:\n";
    if ($result) {
        $rasio = max(1, intval($result['rasio_konversi']));
        $stokFisikKecil = intval($result['stok_sisa']) + intval($result['stok_reserved_kecil']);
        $stokFisik = max(0, intval(floor($stokFisikKecil / $rasio)));
        $stokTersedia = max(0, intval(floor(intval($result['stok_sisa']) / $rasio)));
        $stokReserved = max(0, intval(floor(intval($result['stok_reserved_kecil']) / $rasio)));

        echo "   [BERHASIL TERDETEKSI]\n";
        echo "   - Nomor Batch         : {$result['no_batch']}\n";
        echo "   - Stok Sisa di Sistem : {$result['stok_sisa']} (KOSONG / 0)\n";
        echo "   - Stok Reserved       : {$result['stok_reserved_kecil']} (TERKUNCI PESANAN)\n";
        echo "   - Stok Fisik Lapangan : $stokFisik {$result['satuan_besar']} ($stokFisikKecil {$result['satuan_kecil']})\n";
        echo "   - Stok Tersedia Opname: $stokTersedia {$result['satuan_besar']}\n";
        echo "   - Stok Reserved Opname: $stokReserved {$result['satuan_besar']}\n";
        echo "   => Terbukti rumus (stok_sisa + reserved > 0) membawa batch dengan stok_sisa = 0 ke dalam sesi opname!\n";
    } else {
        echo "   [GAGAL] Batch tidak ditemukan dalam snapshot opname!\n";
    }

    // Rollback agar data uji tidak mengotori database produksi
    $pdo->rollBack();
    echo "\n3. Status DB: Rollback sukses. Data uji bersih kembali.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}
