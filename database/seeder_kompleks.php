<?php
require_once '../config/config.php';
require_once '../config/koneksi.php';

try {
    $pdo->beginTransaction();

    echo "Memulai Proses TRUNCATE (Wipe Out)...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE detail_penjualan;");
    $pdo->exec("TRUNCATE TABLE penjualan;");
    $pdo->exec("TRUNCATE TABLE log_anomali_fefo;");
    $pdo->exec("TRUNCATE TABLE kartu_stok;");
    $pdo->exec("TRUNCATE TABLE stok_batch;");
    $pdo->exec("TRUNCATE TABLE unit_serial;");
    $pdo->exec("TRUNCATE TABLE stok_toko;");
    $pdo->exec("TRUNCATE TABLE produk_variasi;");
    $pdo->exec("TRUNCATE TABLE produk_induk;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "TRUNCATE Berhasil.\n\n";

    function tambahInduk($sku, $nama, $kat, $bhe, $desc = '') {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO produk_induk (sku_induk, nama_produk, kategori, batas_hari_expired, deskripsi, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$sku, $nama, $kat, $bhe, $desc]);
        return $pdo->lastInsertId();
    }

    function tambahVariasi($idInduk, $sku, $nama, $satKecil, $satBesar, $rasio, $hrgKecil, $hrgBesar, $berat, $stokMin) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO produk_variasi (id_produk_induk, sku_variasi, nama_variasi, satuan_kecil, satuan_besar, rasio_konversi, harga_jual_kecil, harga_jual_besar, berat, stok_minimum, tampil_di_online, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)");
        $stmt->execute([$idInduk, $sku, $nama, $satKecil, $satBesar, $rasio, $hrgKecil, $hrgBesar, $berat, $stokMin]);
        $idVar = $pdo->lastInsertId();
        
        $pdo->prepare("INSERT INTO stok_toko (id_variasi, stok) VALUES (?, 0)")->execute([$idVar]);
        return $idVar;
    }

    function tambahBatch($idVar, $noBatch, $tglMasuk, $tglExp, $qty) {
        global $pdo;
        $pdo->prepare("INSERT INTO stok_batch (id_variasi, no_batch, tgl_exp, stok_sisa, created_at) VALUES (?, ?, ?, ?, ?)")->execute([$idVar, $noBatch, $tglExp, $qty, $tglMasuk . ' 10:00:00']);
        $pdo->prepare("UPDATE stok_toko SET stok = stok + ? WHERE id_variasi = ?")->execute([$qty, $idVar]);
    }
    
    function tambahSN($idVar, $sn, $tglMasuk, $tglGaransi = null, $status = 'Tersedia') {
        global $pdo;
        $pdo->prepare("INSERT INTO unit_serial (id_variasi, serial_number, status, tgl_habis_garansi, created_at) VALUES (?, ?, ?, ?, ?)")->execute([$idVar, $sn, $status, $tglGaransi, $tglMasuk . ' 10:00:00']);
        $pdo->prepare("UPDATE stok_toko SET stok = stok + 1 WHERE id_variasi = ?")->execute([$idVar]);
    }

    echo "Generate KATEGORI 1: OBAT SIRUP...\n";
    $idHufagrip = tambahInduk('OB-HFG-001', 'Hufagrip 60ml', 'Obat', 90);
    $varHfg1 = tambahVariasi($idHufagrip, 'HFG-KUN', 'Hufagrip Flu & Batuk (Kuning)', 'Botol', 'Karton', 24, 15000, 350000, 150, 10);
    $varHfg2 = tambahVariasi($idHufagrip, 'HFG-HIJ', 'Hufagrip BP (Hijau)', 'Botol', 'Karton', 24, 15000, 350000, 150, 10);
    $varHfg3 = tambahVariasi($idHufagrip, 'HFG-MER', 'Hufagrip TMP (Merah)', 'Botol', 'Karton', 24, 15000, 350000, 150, 10);
    $varHfg4 = tambahVariasi($idHufagrip, 'HFG-BIR', 'Hufagrip Pilek (Biru)', 'Botol', 'Karton', 24, 15000, 350000, 150, 10);
    tambahBatch($varHfg1, 'HFG-KUN-A1', '2026-09-01', '2028-09-01', 50);
    tambahBatch($varHfg1, 'HFG-KUN-A2', '2026-09-01', date('Y-m-d', strtotime('+10 days')), 15); // Hampir Expired!
    tambahBatch($varHfg2, 'HFG-HIJ-A1', '2026-09-01', '2028-09-01', 30);
    tambahBatch($varHfg3, 'HFG-MER-A1', '2026-09-01', '2028-09-01', 5); // Stok Menipis (Di bawah ROP 10)

    $idSanmol = tambahInduk('OB-SAN-001', 'Sanmol Sirup', 'Obat', 60);
    $varSan1 = tambahVariasi($idSanmol, 'SAN-60', 'Sanmol Sirup 60ml', 'Botol', 'Karton', 24, 18000, 420000, 150, 15);
    $varSan2 = tambahVariasi($idSanmol, 'SAN-15', 'Sanmol Drops 15ml', 'Botol', 'Karton', 24, 22000, 500000, 50, 10);
    tambahBatch($varSan1, 'SAN-60-B1', '2026-09-01', '2027-12-01', 40);
    tambahBatch($varSan2, 'SAN-15-B1', '2026-09-01', '2027-12-01', 8); // Stok Menipis

    echo "Generate KATEGORI 2: OBAT TABLET/KAPSUL (MULTI-TIER)...\n";
    $idAmox = tambahInduk('OB-AMX-001', 'Amoxicillin Trihydrate', 'Obat', 120);
    $varAmx1 = tambahVariasi($idAmox, 'AMX-500', 'Amoxicillin 500mg Kapsul', 'Strip', 'Box (10 Strip)', 10, 7000, 65000, 50, 20);
    $varAmx2 = tambahVariasi($idAmox, 'AMX-250', 'Amoxicillin 250mg Kapsul', 'Strip', 'Box (10 Strip)', 10, 5000, 45000, 50, 20);
    tambahBatch($varAmx1, 'AMX-2024-A', '2024-01-01', '2026-12-01', 5);
    tambahBatch($varAmx1, 'AMX-2024-B', '2024-03-01', '2027-08-01', 5);
    tambahBatch($varAmx1, 'AMX-2024-C', '2024-06-01', '2028-01-01', 5);
    tambahBatch($varAmx2, 'AMX250-C1', '2026-09-01', '2029-01-01', 50);

    $idAsam = tambahInduk('OB-ASM-001', 'Asam Mefenamat', 'Obat', 120);
    $varAsm = tambahVariasi($idAsam, 'ASM-500', 'Asam Mefenamat 500mg Kaplet', 'Strip', 'Box (10 Strip)', 10, 4000, 38000, 50, 20);
    tambahBatch($varAsm, 'ASM500-D1', '2026-09-01', '2028-12-01', 80);

    $idAmlo = tambahInduk('OB-AML-001', 'Amlodipine Besylate', 'Obat', 120);
    $varAml1 = tambahVariasi($idAmlo, 'AML-5', 'Amlodipine 5mg', 'Strip', 'Box (3 Strip)', 3, 15000, 43000, 20, 10);
    $varAml2 = tambahVariasi($idAmlo, 'AML-10', 'Amlodipine 10mg', 'Strip', 'Box (3 Strip)', 3, 25000, 72000, 20, 10);
    tambahBatch($varAml1, 'AML5-E1', '2026-09-01', date('Y-m-d', strtotime('+7 days')), 12); // Hampir Expired
    tambahBatch($varAml2, 'AML10-E1', '2026-09-01', '2028-06-01', 40);

    echo "Generate KATEGORI 3: OBAT BEBAS / HERBAL...\n";
    $idTolak = tambahInduk('OB-TOL-001', 'Tolak Angin', 'Obat', 180);
    $varTol1 = tambahVariasi($idTolak, 'TOL-KUN', 'Tolak Angin Cair Dus Kuning', 'Sachet', 'Box (12 Sachet)', 12, 4000, 46000, 25, 24);
    $varTol2 = tambahVariasi($idTolak, 'TOL-ANK', 'Tolak Angin Anak', 'Sachet', 'Box (12 Sachet)', 12, 3500, 40000, 25, 24);
    $varTol3 = tambahVariasi($idTolak, 'TOL-FLU', 'Tolak Angin Flu', 'Sachet', 'Box (12 Sachet)', 12, 4500, 52000, 25, 24);
    tambahBatch($varTol1, 'TOLKUN-F1', '2026-08-01', '2027-08-01', 144);
    tambahBatch($varTol1, 'TOLKUN-F2', '2026-09-01', '2028-01-01', 72);
    tambahBatch($varTol2, 'TOLANK-F1', '2026-08-01', '2027-08-01', 48);

    $idAnt = tambahInduk('OB-ANT-001', 'Antangin JRG', 'Obat', 180);
    $varAnt1 = tambahVariasi($idAnt, 'ANT-CAIR', 'Antangin JRG Cair', 'Sachet', 'Box (12 Sachet)', 12, 3800, 44000, 25, 24);
    $varAnt2 = tambahVariasi($idAnt, 'ANT-TAB', 'Antangin JRG Tablet', 'Strip', 'Box (20 Strip)', 20, 2500, 48000, 30, 40);
    tambahBatch($varAnt1, 'ANTC-G1', '2026-08-01', '2027-08-01', 120);
    tambahBatch($varAnt2, 'ANTT-G1', '2026-08-01', '2027-08-01', 200);

    echo "Generate KATEGORI 4: ALKES BMHP (PAKAI BATCH & EXPIRED)...\n";
    $idEasy = tambahInduk('AK-EST-001', 'Strip Test EasyTouch', 'Alat Kesehatan', 90);
    $varEasy1 = tambahVariasi($idEasy, 'EST-GLU', 'Strip Gula Darah (Blood Glucose)', 'Botol (Isi 25)', 'Box (2 Botol)', 2, 85000, 160000, 100, 4);
    $varEasy2 = tambahVariasi($idEasy, 'EST-URI', 'Strip Asam Urat (Uric Acid)', 'Botol (Isi 25)', 'Box (1 Botol)', 1, 95000, 95000, 80, 2);
    $varEasy3 = tambahVariasi($idEasy, 'EST-CHO', 'Strip Kolesterol', 'Botol (Isi 10)', 'Box (1 Botol)', 1, 150000, 150000, 80, 2);
    tambahBatch($varEasy1, 'EST-GLU-H1', '2026-09-01', '2027-05-01', 10);
    tambahBatch($varEasy2, 'EST-URI-H1', '2026-09-01', date('Y-m-d', strtotime('+8 days')), 1); // Hampir Expired & Menipis
    tambahBatch($varEasy3, 'EST-CHO-H1', '2026-09-01', '2027-05-01', 5);

    $idSensi = tambahInduk('AK-SEN-001', 'Masker Medis Sensi', 'Alat Kesehatan', 90);
    $varSensi1 = tambahVariasi($idSensi, 'SEN-EAR', 'Sensi Earloop 3-Ply (Hijau)', 'Box (Isi 50)', 'Karton (40 Box)', 40, 35000, 1300000, 300, 10);
    $varSensi2 = tambahVariasi($idSensi, 'SEN-DUC', 'Sensi Duckbill (Putih)', 'Box', 'Karton (40 Box)', 40, 45000, 1700000, 350, 10);
    tambahBatch($varSensi1, 'SENEAR-I1', '2026-09-01', '2030-01-01', 80);
    tambahBatch($varSensi2, 'SENDUC-I1', '2026-09-01', '2030-01-01', 120);

    echo "Generate KATEGORI 5: ALKES EQUIPMENT (TANPA EXPIRED - PAKAI SN)...\n";
    $idTermo = tambahInduk('AK-TRM-001', 'Termometer Omron', 'Alat Kesehatan', 0);
    $varTermo1 = tambahVariasi($idTermo, 'TRM-246', 'Termometer Digital Omron MC-246', 'Pcs', 'Pcs', 1, 75000, 75000, 100, 5);
    $varTermo2 = tambahVariasi($idTermo, 'TRM-720', 'Termometer Tembak Omron MC-720', 'Pcs', 'Pcs', 1, 450000, 450000, 250, 2);
    for ($i=1; $i<=8; $i++) { tambahSN($varTermo1, 'SN-TRM246-00'.$i, '2026-09-01'); }
    for ($i=1; $i<=3; $i++) { tambahSN($varTermo2, 'SN-TRM720-00'.$i, '2026-09-01'); }

    $idTensi = tambahInduk('AK-TNS-001', 'Tensimeter Digital Omron', 'Alat Kesehatan', 0);
    $varTensi1 = tambahVariasi($idTensi, 'TNS-7120', 'Tensimeter HEM-7120', 'Unit', 'Unit', 1, 650000, 650000, 800, 2);
    $varTensi2 = tambahVariasi($idTensi, 'TNS-7156', 'Tensimeter HEM-7156 (Bluetooth)', 'Unit', 'Unit', 1, 950000, 950000, 900, 2);
    tambahSN($varTensi1, 'OMR-HEM7120-001', '2026-09-01', date('Y-m-d', strtotime('+1 months'))); // Garansi hampir habis
    tambahSN($varTensi1, 'OMR-HEM7120-002', '2026-09-01', '2028-08-15');
    tambahSN($varTensi2, 'SN-HEM7156-Y1', '2026-09-01', '2029-01-01'); // 1 unit -> Stok Menipis!

    $idKursi = tambahInduk('AK-KRS-001', 'Kursi Roda Gea', 'Alat Kesehatan', 0);
    $varKursi1 = tambahVariasi($idKursi, 'KRS-809', 'Kursi Roda Standar FS809', 'Unit', 'Unit', 1, 1200000, 1200000, 15000, 1);
    $varKursi2 = tambahVariasi($idKursi, 'KRS-TRV', 'Kursi Roda Travel Lipat', 'Unit', 'Unit', 1, 1850000, 1850000, 12000, 1);
    tambahSN($varKursi1, 'SN-KRS809-Z1', '2026-09-01');
    tambahSN($varKursi2, 'SN-KRSTRV-Z1', '2026-09-01');

    $pdo->commit();
    echo "\nSEEDER BERHASIL DIEKSEKUSI. TOTAL 50+ VARIASI TERBUAT!\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "ERROR SEEDER: " . $e->getMessage() . "\n";
}
