<?php
// seeder_v2_real.php
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

    // SKENARIO 1 (Amoxicillin)
    $idAmx = insertInduk($pdo, 'Amoxicillin 500mg', 'Obat', 'Antibiotik Kapsul (Wajib Resep)', 90);
    $varAmx = insertVar($pdo, $idAmx, 'AMX-500', 'Tablet 500mg', 'Strip', 'Box', 10, 5000, 48000, 10);
    insertBatch($pdo, $varAmx, 'AMX-2025.A', '2025-01-10', 8);
    insertBatch($pdo, $varAmx, 'AMX-2025.B', '2025-01-10', 10);
    insertBatch($pdo, $varAmx, 'AMX-2026', '2026-12-15', 50);

    // SKENARIO 2 (Sanmol Sirup)
    $idSan = insertInduk($pdo, 'Sanmol Sirup Anak 60ml', 'Obat', 'Obat Demam Anak Rasa Stroberi', 180);
    $varSan = insertVar($pdo, $idSan, 'SAN-SYR', 'Botol 60ml', 'Botol', 'Karton', 24, 15000, 350000, 10);
    insertBatch($pdo, $varSan, 'SAN-001', '2026-08-01', 48);
    insertBatch($pdo, $varSan, 'SAN-001.A', '2026-08-01', 15);
    insertBatch($pdo, $varSan, 'SAN-002', '2028-05-10', 120);

    // SKENARIO 3 (EasyTouch & Omron)
    $idEasy = insertInduk($pdo, 'Strip Gula Darah EasyTouch', 'Alat Kesehatan', 'BMHP Gula Darah - Isi 25', 90);
    $varEasy = insertVar($pdo, $idEasy, 'ESY-GLU', 'Tube (25 Pcs)', 'Tube', 'Box', 10, 85000, 800000, 5);
    insertBatch($pdo, $varEasy, 'EASY-GLUCO-01', '2027-10-10', 50);
    insertBatch($pdo, $varEasy, 'EASY-GLUCO-01.A', '2027-10-10', 1);

    $idTensi = insertInduk($pdo, 'Tensimeter Digital Omron', 'Alat Kesehatan', 'Alat Ukur Tekanan Darah Lengan', 0);
    $varTensi = insertVar($pdo, $idTensi, 'OMR-TENS', 'Unit HEM-7120', 'Unit', 'Karton', 5, 550000, 2600000, 2);
    insertSN($pdo, $varTensi, 'OMR-111001', '2028-01-01');
    insertSN($pdo, $varTensi, 'OMR-111002', '2028-01-01');
    insertSN($pdo, $varTensi, 'OMR-111003', '2028-01-01');
    insertSN($pdo, $varTensi, 'OMR-111004', '2028-01-01');
    insertSN($pdo, $varTensi, 'OMR-111005', $tglDepan);

    $idThermo = insertInduk($pdo, 'Termometer Digital Omron', 'Alat Kesehatan', 'Termometer Badan Digital MC-246', 0);
    $varThermo = insertVar($pdo, $idThermo, 'OMR-THER', 'Unit MC-246', 'Unit', 'Karton', 10, 85000, 800000, 3);
    for ($i=1; $i<=5; $i++) { insertSN($pdo, $varThermo, 'OMR-TH-'.$i, '2027-06-15'); }

    // Hufagrip Variasi
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

    // Paracetamol Variasi
    $pct = insertInduk($pdo, 'Paracetamol', 'Obat', 'Pereda Nyeri / Penurun Panas', 90);
    $pct500 = insertVar($pdo, $pct, 'PCT-500', 'Tablet 500mg', 'Strip', 'Box', 10, 3500, 30000);
    $pct250 = insertVar($pdo, $pct, 'PCT-250', 'Tablet 250mg', 'Strip', 'Box', 10, 2500, 22000);
    $pct125 = insertVar($pdo, $pct, 'PCT-125', 'Tablet 125mg', 'Strip', 'Box', 10, 2000, 18000);
    foreach([$pct500, $pct250, $pct125] as $idx => $v) {
        insertBatch($pdo, $v, "PCT-25-$idx", '2025-11-01', 50);
        insertBatch($pdo, $v, "PCT-25-$idx.A", '2025-11-01', 5);
        insertBatch($pdo, $v, "PCT-26-$idx", '2026-11-01', 100);
    }

    // REAL DATA Obat (Banyak)
    $realObat = [
        ['Promag Tablet', 'Obat Sakit Maag & Kembung', 'PRM-01', 'Tablet Kunyah', 'Strip', 'Box', 12, 9500, 110000],
        ['Panadol Extra', 'Pereda Sakit Kepala Membandel', 'PND-EXT', 'Kaplet Merah', 'Strip', 'Box', 10, 12500, 120000],
        ['Tolak Angin Cair', 'Obat Masuk Angin', 'TLK-ANG', 'Sachet 15ml', 'Sachet', 'Box', 12, 4500, 52000],
        ['Mylanta Cair 150ml', 'Obat Maag Cair', 'MYL-150', 'Botol 150ml', 'Botol', 'Karton', 24, 45000, 1050000],
        ['Insto Reguler 7.5ml', 'Tetes Mata Merah', 'INS-REG', 'Botol 7.5ml', 'Botol', 'Box', 12, 16000, 185000],
        ['Betadine Solution 15ml', 'Antiseptik Luka', 'BET-15', 'Botol 15ml', 'Botol', 'Box', 12, 12500, 145000],
        ['Counterpain Krim 30g', 'Krim Pereda Nyeri Otot', 'CTP-30', 'Tube 30g', 'Tube', 'Box', 10, 55000, 530000],
        ['Neurobion Forte', 'Vitamin B Kompleks', 'NEU-FRT', 'Tablet Merah', 'Strip', 'Box', 10, 48000, 460000],
        ['Sangobion Kapsul', 'Suplemen Zat Besi', 'SNG-CAP', 'Kapsul Merah', 'Strip', 'Box', 25, 18500, 450000],
        ['Diapet Kapsul', 'Obat Diare', 'DPT-CAP', 'Kapsul Isi 10', 'Strip', 'Box', 25, 4500, 105000],
        ['Entrostop', 'Obat Diare Dewasa', 'ENT-DSW', 'Tablet', 'Strip', 'Box', 20, 9500, 180000],
        ['Woods Peppermint Syr', 'Obat Batuk Antitusif', 'WOD-PEP', 'Botol 100ml', 'Botol', 'Karton', 24, 38000, 890000],
        ['Komix Herbal', 'Sirup Obat Batuk Tube', 'KMX-HRB', 'Sachet 15ml', 'Sachet', 'Box', 30, 2500, 70000],
        ['Antangin JRG', 'Obat Herbal Masuk Angin', 'ANT-JRG', 'Sachet 15ml', 'Sachet', 'Box', 12, 4000, 46000],
        ['CDR Effervescent', 'Vitamin C & Kalsium', 'CDR-EFT', 'Tube Isi 15', 'Tube', 'Karton', 10, 65000, 630000],
        ['Redoxon Double Action', 'Multivitamin C & Zinc', 'RDX-DBL', 'Tube Isi 10', 'Tube', 'Karton', 10, 55000, 530000],
        ['Enervon-C Multivitamin', 'Vitamin C & B Kompleks', 'ENV-C', 'Strip Isi 4', 'Strip', 'Box', 25, 6000, 145000],
        ['Imboost Force', 'Suplemen Daya Tahan Tubuh', 'IMB-FRC', 'Strip Isi 10', 'Strip', 'Box', 3, 85000, 245000],
        ['Bodrex Migra', 'Obat Sakit Kepala Migrain', 'BDR-MGR', 'Strip Isi 4', 'Strip', 'Box', 25, 3500, 85000],
        ['Neozep Forte', 'Obat Flu & Pilek', 'NZP-FRT', 'Strip Isi 4', 'Strip', 'Box', 25, 3500, 85000],
        ['Decolgen', 'Obat Flu Ringan', 'DCL-GEN', 'Strip Isi 4', 'Strip', 'Box', 25, 3000, 72000],
        ['OBH Combi Plus', 'Sirup Batuk & Flu', 'OBH-CPL', 'Botol 100ml', 'Botol', 'Karton', 24, 25000, 580000],
        ['Salonpas Koyo', 'Koyo Pereda Nyeri', 'SLN-PAS', 'Sachet Isi 10', 'Sachet', 'Box', 40, 8500, 320000],
        ['Vicks VapoRub 10g', 'Balsem Pelega Tenggorokan', 'VK-VP10', 'Pot 10g', 'Pot', 'Box', 24, 12000, 275000],
        ['Tolak Linu', 'Obat Pegal Linu Cair', 'TLK-LNU', 'Sachet 15ml', 'Sachet', 'Box', 12, 4500, 52000],
        ['Decadryl Expectorant', 'Obat Batuk Berdahak', 'DCD-EXP', 'Botol 120ml', 'Botol', 'Karton', 24, 22000, 510000],
        ['Polysilane Kapsul', 'Obat Asam Lambung', 'PLS-CAP', 'Strip Isi 10', 'Strip', 'Box', 10, 12000, 115000],
        ['Combantrin Jeruk 10ml', 'Obat Cacing Sirup Anak', 'CMB-JRK', 'Botol 10ml', 'Botol', 'Box', 12, 21000, 245000],
        ['Cefadroxil 500mg', 'Antibiotik (Resep Dokter)', 'CFD-500', 'Strip Isi 10', 'Strip', 'Box', 10, 18000, 170000],
        ['CTM', 'Obat Alergi Ringan', 'CTM-ALR', 'Strip Isi 12', 'Strip', 'Box', 10, 2500, 23000],
        ['Cetirizine 10mg', 'Obat Alergi / Antihistamin', 'CTR-10', 'Strip Isi 10', 'Strip', 'Box', 10, 8500, 80000]
    ];

    $batchIdx = 1;
    foreach ($realObat as $ro) {
        $idO = insertInduk($pdo, $ro[0], 'Obat', $ro[1]);
        $vO = insertVar($pdo, $idO, $ro[2], $ro[3], $ro[4], $ro[5], $ro[6], $ro[7], $ro[8]);
        // Bikin batch
        insertBatch($pdo, $vO, "BCH-25-".$batchIdx, '2025-08-01', 50 * $ro[6]); // 50 Box utuh
        insertBatch($pdo, $vO, "BCH-25-".$batchIdx.".A", '2025-08-01', 3); // 3 Strip eceran
        insertBatch($pdo, $vO, "BCH-26-".$batchIdx, '2026-10-15', 100 * $ro[6]); // 100 Box utuh
        $batchIdx++;
    }

    // REAL DATA Alat Kesehatan & BMHP
    $realAlkes = [
        ['Masker Sensi Earloop', 'Masker Medis 3 Ply (Isi 50)', 'SNS-MSK', 'Box Isi 50', 'Box', 'Karton', 40, 25000, 950000],
        ['Hansaplast Kain Elastis', 'Plester Luka', 'HNS-PLS', 'Box Isi 100', 'Lembar', 'Box', 100, 800, 75000],
        ['Plester Dermafix', 'Plester Anti Air Transparan', 'DRM-FIX', 'Pcs 5x7cm', 'Pcs', 'Box', 25, 4500, 105000],
        ['Kasa Steril Onemed', 'Kasa Luka Steril 16x16', 'OM-KSA', 'Kotak Isi 10', 'Kotak', 'Karton', 50, 12000, 580000],
        ['Alkohol Onemed 70% 100ml', 'Cairan Antiseptik', 'OM-ALK', 'Botol 100ml', 'Botol', 'Karton', 24, 8500, 195000],
        ['Rivanol 100ml', 'Cairan Pembersih Luka', 'RVN-100', 'Botol 100ml', 'Botol', 'Box', 24, 7500, 175000],
        ['Povidone Iodine 1 Liter', 'Betadine Literan RS', 'PVD-1L', 'Botol 1L', 'Botol', 'Karton', 10, 125000, 1200000],
        ['Alat Cek Asam Urat EasyTouch', 'BMHP Asam Urat - Isi 25', 'ESY-UA', 'Tube Isi 25', 'Tube', 'Box', 10, 95000, 900000],
        ['Alat Cek Kolesterol Nesco', 'BMHP Kolesterol - Isi 10', 'NSC-CHL', 'Tube Isi 10', 'Tube', 'Box', 10, 135000, 1300000],
    ];
    
    foreach ($realAlkes as $ra) {
        $idA = insertInduk($pdo, $ra[0], 'Alat Kesehatan', $ra[1], 90);
        $vA = insertVar($pdo, $idA, $ra[2], $ra[3], $ra[4], $ra[5], $ra[6], $ra[7], $ra[8]);
        insertBatch($pdo, $vA, "ALK-25-".$batchIdx, '2025-11-20', 20 * $ra[6]);
        insertBatch($pdo, $vA, "ALK-25-".$batchIdx.".A", '2025-11-20', 2);
        $batchIdx++;
    }

    // REAL EQUIPMENT (Pakai SN)
    $realEquipment = [
        ['Kursi Roda Standard Sella', 'Kursi Roda Lipat Ringan', 'SLL-KRD', 'Unit Standar', 'Unit', 'Karton', 1, 1450000, 1400000],
        ['Tabung Oksigen 1m3', 'Tabung O2 Medis Kosong', 'O2-1M3', 'Tabung 1m3', 'Tabung', 'Karton', 1, 750000, 720000],
        ['Regulator Oksigen', 'Regulator Medis O2', 'O2-REG', 'Unit Flowmeter', 'Unit', 'Box', 10, 250000, 2400000],
        ['Nebulizer Omron', 'Alat Terapi Uap Asma NE-C104', 'OMR-NEB', 'Unit NE-C104', 'Unit', 'Karton', 5, 850000, 4100000],
        ['Termometer Infra Merah', 'Thermometer Tembak Dahi', 'IR-THER', 'Unit Pistol', 'Unit', 'Karton', 20, 125000, 2400000],
        ['Stetoskop Littmann', 'Stethoscope Classic III Medis', 'LTM-STC', 'Unit Classic III', 'Unit', 'Box', 10, 1850000, 18000000]
    ];
    
    $eqIdx = 1;
    foreach ($realEquipment as $re) {
        $idE = insertInduk($pdo, $re[0], 'Alat Kesehatan', $re[1], 0);
        $vE = insertVar($pdo, $idE, $re[2], $re[3], $re[4], $re[5], $re[6], $re[7], $re[8]);
        for ($k=1; $k<=3; $k++) {
            insertSN($pdo, $vE, "SN-".$re[2]."-100$k", '2028-12-31');
        }
    }

    $pdo->commit();
    echo "SEEDER V2 (REAL DATA) BERHASIL DIEKSEKUSI!";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "GAGAL: " . $e->getMessage();
}


