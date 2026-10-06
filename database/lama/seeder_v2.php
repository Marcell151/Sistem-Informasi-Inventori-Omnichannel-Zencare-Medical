<?php
require_once __DIR__ . '/../config/koneksi.php';
try {
    // Drop constraints for a bit
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE log_anomali_fefo;");
    $pdo->exec("TRUNCATE TABLE kartu_stok;");
    $pdo->exec("TRUNCATE TABLE unit_serial;");
    $pdo->exec("TRUNCATE TABLE stok_batch;");
    $pdo->exec("TRUNCATE TABLE stok_toko;");
    $pdo->exec("TRUNCATE TABLE detail_penjualan;");
    $pdo->exec("TRUNCATE TABLE penjualan;");
    $pdo->exec("TRUNCATE TABLE produk_variasi;");
    $pdo->exec("TRUNCATE TABLE produk_induk;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    // Now begin transaction for data insertion
    $pdo->beginTransaction();

    $tglMasuk = date('Y-m-d');
    $tglDepan = date('Y-m-d', strtotime('+1 month'));

    function insertInduk($pdo, $nama, $kategori, $deskripsi, $batasExpired = 90) {
        $skuInduk = 'IND-' . strtoupper(substr(md5(uniqid()), 0, 6));
        $stmt = $pdo->prepare("INSERT INTO produk_induk (sku_induk, nama_produk, kategori, deskripsi, batas_hari_expired, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$skuInduk, $nama, $kategori, $deskripsi, $batasExpired]);
        return $pdo->lastInsertId();
    }

    function insertVar($pdo, $idInduk, $sku, $namaVar, $sKecil, $sBesar, $rasio, $hKecil, $hBesar, $minStok = 5, $minStokBesar = 2) {
        $stmt = $pdo->prepare("INSERT INTO produk_variasi (id_produk_induk, sku_variasi, nama_variasi, satuan_kecil, satuan_besar, rasio_konversi, harga_jual_kecil, harga_jual_besar, stok_minimum_kecil, stok_minimum_besar, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$idInduk, $sku, $namaVar, $sKecil, $sBesar, $rasio, $hKecil, $hBesar, $minStok, $minStokBesar]);
        $idVar = $pdo->lastInsertId();
        $pdo->prepare("INSERT INTO stok_toko (id_variasi, stok) VALUES (?, 0)")->execute([$idVar]);
        return $idVar;
    }

    function insertBatch($pdo, $idVar, $noBatch, $tglExp, $qty) {
        $pdo->prepare("INSERT INTO stok_batch (id_variasi, no_batch, tgl_exp, stok_sisa) VALUES (?, ?, ?, ?)")->execute([$idVar, $noBatch, $tglExp, $qty]);
        $pdo->prepare("UPDATE stok_toko SET stok = stok + ? WHERE id_variasi = ?")->execute([$qty, $idVar]);
    }

    function insertSN($pdo, $idVar, $sn, $tglGaransi = null) {
        $pdo->prepare("INSERT INTO unit_serial (id_variasi, serial_number, status, tgl_habis_garansi) VALUES (?, ?, 'Tersedia', ?)")->execute([$idVar, $sn, $tglGaransi]);
        $pdo->prepare("UPDATE stok_toko SET stok = stok + 1 WHERE id_variasi = ?")->execute([$idVar]);
    }

    // SKENARIO 1
    $idAmx = insertInduk($pdo, 'Amoxicillin 500mg', 'Obat', 'Antibiotik Kapsul', 90);
    $varAmx = insertVar($pdo, $idAmx, 'AMX-500', 'Tablet 500mg', 'Strip', 'Box', 10, 5000, 48000, 10);
    insertBatch($pdo, $varAmx, 'AMX-2025.A', '2025-01-10', 8);
    insertBatch($pdo, $varAmx, 'AMX-2025.B', '2025-01-10', 10);
    insertBatch($pdo, $varAmx, 'AMX-2026', '2026-12-15', 50);

    // SKENARIO 2
    $idSan = insertInduk($pdo, 'Sanmol Sirup Anak 60ml', 'Obat', 'Obat Demam Anak', 180);
    $varSan = insertVar($pdo, $idSan, 'SAN-SYR', 'Botol 60ml', 'Botol', 'Karton', 24, 15000, 350000, 10);
    insertBatch($pdo, $varSan, 'SAN-001', '2026-08-01', 48);
    insertBatch($pdo, $varSan, 'SAN-001.A', '2026-08-01', 15);
    insertBatch($pdo, $varSan, 'SAN-002', '2028-05-10', 120);

    // SKENARIO 3
    $idEasy = insertInduk($pdo, 'Strip Gula Darah EasyTouch', 'Alat Kesehatan', 'BMHP Gula Darah', 90);
    $varEasy = insertVar($pdo, $idEasy, 'ESY-GLU', 'Tube (25 Pcs)', 'Tube', 'Box', 10, 85000, 800000, 5);
    insertBatch($pdo, $varEasy, 'EASY-GLUCO-01', '2027-10-10', 50);
    insertBatch($pdo, $varEasy, 'EASY-GLUCO-01.A', '2027-10-10', 1);

    $idTensi = insertInduk($pdo, 'Tensimeter Digital Omron', 'Alat Kesehatan', 'Alat Ukur Tekanan Darah', 0);
    $varTensi = insertVar($pdo, $idTensi, 'OMR-TENS', 'Unit', 'Unit', 'Karton', 5, 550000, 2600000, 2);
    insertSN($pdo, $varTensi, 'OMR-111001', '2028-01-01');
    insertSN($pdo, $varTensi, 'OMR-111002', '2028-01-01');
    insertSN($pdo, $varTensi, 'OMR-111003', '2028-01-01');
    insertSN($pdo, $varTensi, 'OMR-111004', '2028-01-01');
    insertSN($pdo, $varTensi, 'OMR-111005', $tglDepan);

    $idThermo = insertInduk($pdo, 'Termometer Digital Omron', 'Alat Kesehatan', 'Termometer Badan', 0);
    $varThermo = insertVar($pdo, $idThermo, 'OMR-THER', 'Unit', 'Unit', 'Karton', 10, 85000, 800000, 3);
    for ($i=1; $i<=5; $i++) { insertSN($pdo, $varThermo, 'OMR-TH-'.$i, '2027-06-15'); }

    // MASSIVE
    $hufa = insertInduk($pdo, 'Hufagrip', 'Obat', 'Obat Flu Anak', 180);
    $hufaMerah = insertVar($pdo, $hufa, 'HUF-TMP', 'Hufagrip Merah (TMP)', 'Botol', 'Karton', 50, 18000, 850000);
    $hufaBiru = insertVar($pdo, $hufa, 'HUF-PIL', 'Hufagrip Biru (Pilek)', 'Botol', 'Karton', 50, 18000, 850000);
    $hufaHijau = insertVar($pdo, $hufa, 'HUF-BP', 'Hufagrip Hijau (BP)', 'Botol', 'Karton', 50, 18000, 850000);
    $hufaKuning = insertVar($pdo, $hufa, 'HUF-FLU', 'Hufagrip Kuning (Flu Batuk)', 'Botol', 'Karton', 50, 18000, 850000);
    
    $vars = [$hufaMerah, $hufaBiru, $hufaHijau, $hufaKuning];
    foreach($vars as $idx => $v) {
        insertBatch($pdo, $v, "HUF-2026-0$idx", '2026-10-10', 200);
        insertBatch($pdo, $v, "HUF-2026-0$idx.A", '2026-10-10', 12);
        insertBatch($pdo, $v, "HUF-2027-0$idx", '2027-10-10', 300);
    }

    $pct = insertInduk($pdo, 'Paracetamol', 'Obat', 'Pereda Nyeri', 90);
    $pct500 = insertVar($pdo, $pct, 'PCT-500', 'Tablet 500mg', 'Strip', 'Box', 10, 3000, 25000);
    $pct250 = insertVar($pdo, $pct, 'PCT-250', 'Tablet 250mg', 'Strip', 'Box', 10, 2500, 20000);
    $pct125 = insertVar($pdo, $pct, 'PCT-125', 'Tablet 125mg', 'Strip', 'Box', 10, 2000, 18000);
    foreach([$pct500, $pct250, $pct125] as $idx => $v) {
        insertBatch($pdo, $v, "PCT-25-$idx", '2025-11-01', 50);
        insertBatch($pdo, $v, "PCT-25-$idx.A", '2025-11-01', 5);
        insertBatch($pdo, $v, "PCT-26-$idx", '2026-11-01', 100);
    }

    $salep = insertInduk($pdo, 'Salep Kulit 88', 'Obat', 'Obat Jamur', 90);
    $varSalep = insertVar($pdo, $salep, 'SLP-88', 'Tube 10g', 'Tube', 'Box', 12, 12000, 135000);
    insertBatch($pdo, $varSalep, 'SLP-27', '2027-01-01', 60);
    insertBatch($pdo, $varSalep, 'SLP-27.A', '2027-01-01', 3);

    for($i=1; $i<=30; $i++) {
        $id = insertInduk($pdo, "Obat Generik Dummy $i", 'Obat', "Deskripsi Dummy $i");
        $vid = insertVar($pdo, $id, "DUM-OBT-$i", "Varian $i", 'Strip', 'Box', 10, 5000, 45000);
        insertBatch($pdo, $vid, "B-DUM-$i", '2026-05-01', 100);
        insertBatch($pdo, $vid, "B-DUM-$i.A", '2026-05-01', 7);
    }

    for($i=1; $i<=20; $i++) {
        $id = insertInduk($pdo, "Alkes Equipment Dummy $i", 'Alat Kesehatan', "Alat Dummy $i", 0);
        $vid = insertVar($pdo, $id, "DUM-ALK-$i", "Unit $i", 'Unit', 'Box', 5, 100000, 480000);
        insertSN($pdo, $vid, "SN-DUM-$i-1", '2028-12-31');
        insertSN($pdo, $vid, "SN-DUM-$i-2", '2028-12-31');
    }

    $pdo->commit();
    echo "SEEDER V2 BERHASIL DIEKSEKUSI! Total 50+ Produk Induk dan Ratusan Stok Batch/SN disuntikkan.";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "GAGAL: " . $e->getMessage();
}
