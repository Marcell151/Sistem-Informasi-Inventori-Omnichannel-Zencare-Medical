<?php
require_once __DIR__ . '/../config/koneksi.php';

echo "=== PENGUJIAN KASUS EKSTREM OPNAME: ALKES SN TERSEDIA = 0 & SN RESERVED PENUH ===\n\n";

$pdo->beginTransaction();
try {
    // 1. Pilih variasi Alkes aktif, misal ID 5 (Termometer Digital Omron)
    $idVar = 5;

    // Hitung kondisi awal
    $initTersedia = intval($pdo->query("SELECT COUNT(*) FROM unit_serial WHERE id_variasi = $idVar AND status = 'Tersedia'")->fetchColumn());
    echo "Kondisi awal SN Tersedia untuk variasi ID $idVar: $initTersedia Unit\n";

    // 2. Buat unit SN uji dengan status 'Tersedia'
    $pdo->prepare("
        INSERT INTO unit_serial (id_variasi, serial_number, status, created_at)
        VALUES (?, 'SN-ZERO-TEST-01', 'Tersedia', NOW())
    ")->execute([$idVar]);
    $testSnId = $pdo->lastInsertId();

    // 3. Buat pesanan status 'Diproses'
    $pdo->prepare("
        INSERT INTO penjualan (no_invoice, id_user, tipe_transaksi, status_pesanan, total_harga, created_at)
        VALUES ('INV-TEST-ALKES-01', 2, 'ecommerce', 'Diproses', 85000, NOW())
    ")->execute();
    $testPenjualanId = $pdo->lastInsertId();

    // 4. Kunci unit serial tersebut ke pesanan ini
    $pdo->prepare("
        UPDATE unit_serial 
        SET id_penjualan = ? 
        WHERE id = ?
    ")->execute([$testPenjualanId, $testSnId]);

    // 5. Ubah semua unit 'Tersedia' lainnya menjadi 'Terjual' sementara dalam transaksi ini agar Tersedia murni = 0
    $pdo->prepare("
        UPDATE unit_serial 
        SET status = 'Terjual' 
        WHERE id_variasi = ? AND id != ? AND status = 'Tersedia'
    ")->execute([$idVar, $testSnId]);

    // Sekarang, untuk variasi ini:
    // sn_tersedia = 0 (karena unit testSnId dikunci ke pesanan Diproses, dan unit lain diubah ke Terjual)
    // sn_reserved = 1 (unit testSnId yang terhubung ke penjualan Diproses)

    echo "Simulasi Kondisi Ekstrem:\n";
    echo "  - sn_tersedia: 0 Unit\n";
    echo "  - sn_reserved: 1 Unit (Terkunci di INV-TEST-ALKES-01)\n\n";

    // 6. Jalankan Query Snapshot Alkes dari inventori/stok_opname.php
    $sqlAlkesSnapshot = "
        SELECT pv.id AS id_variasi, pv.nama_variasi, pi.nama_produk, pv.satuan_besar, pv.satuan_kecil,
               (SELECT COUNT(*) FROM unit_serial us WHERE us.id_variasi = pv.id AND us.status = 'Tersedia' AND us.id_penjualan IS NULL) as sn_tersedia,
               (SELECT COUNT(*) FROM unit_serial us JOIN penjualan p ON us.id_penjualan = p.id WHERE us.id_variasi = pv.id AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')) as sn_reserved
        FROM produk_variasi pv
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        WHERE pv.is_active = 1 
          AND ((SELECT COUNT(*) FROM unit_serial us WHERE us.id_variasi = pv.id AND us.status = 'Tersedia' AND us.id_penjualan IS NULL) + 
               (SELECT COUNT(*) FROM unit_serial us JOIN penjualan p ON us.id_penjualan = p.id WHERE us.id_variasi = pv.id AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil'))) > 0
          AND pv.id = ?
    ";
    $stmtAk = $pdo->prepare($sqlAlkesSnapshot);
    $stmtAk->execute([$idVar]);
    $resAlkes = $stmtAk->fetch(PDO::FETCH_ASSOC);

    echo "Hasil Evaluasi Query Snapshot Opname Alkes:\n";
    if ($resAlkes) {
        $snTersedia = intval($resAlkes['sn_tersedia']);
        $snReserved = intval($resAlkes['sn_reserved']);
        $stokFisikAlkes = $snTersedia + $snReserved;

        echo "   [BERHASIL TERDETEKSI]\n";
        echo "   - Nama Produk         : {$resAlkes['nama_produk']} - {$resAlkes['nama_variasi']}\n";
        echo "   - SN Tersedia Sistem  : $snTersedia Unit\n";
        echo "   - SN Reserved Pesanan : $snReserved Unit\n";
        echo "   - Stok Fisik Lapangan : $stokFisikAlkes Unit\n";
        echo "   => Terbukti Alkes dengan unit tersedia = 0 namun sedang dipesan (Reserved) TETAP MASUK ke dalam opname!\n";
    } else {
        echo "   [GAGAL] Variasi Alkes tidak masuk dalam snapshot opname!\n";
    }

    $pdo->rollBack();
    echo "\nStatus DB: Rollback sukses. Data uji bersih kembali.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}

