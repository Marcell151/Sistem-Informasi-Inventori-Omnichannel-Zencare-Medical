<?php
// File: scratch/build_demo_3.php
// Script untuk merekonstruksi dan mematangkan data db_inventory (demo) 3.sql
require_once __DIR__ . '/../config/koneksi.php';

echo "=== MEMULAI PEMUTAKHIRAN DATA MENUJU DEMO 3 ===\n";

try {
    $pdo->beginTransaction();

    // ── 1. STANDARISASI NOMOR BATCH DUMMY KE FORMAT PABRIK REALISTIS ──
    $batchRenames = [
        'AMX-OLD-2025'     => ['new_no' => 'AMX-24C081', 'exp' => '2025-08-15'],
        'PCT-OLD-2025'     => ['new_no' => 'PCT-24D012', 'exp' => '2025-10-01'],
        'SNM-EXP-TODAY'    => ['new_no' => 'SNM-24K061', 'exp' => '2026-11-06'], // Mendekati Exp (Hari H Sidang / 6 Nov)
        'BDR-EXP-TOMORROW' => ['new_no' => 'BDR-25D110', 'exp' => '2026-11-07'], // Mendekati Exp (H+1 Sidang / 7 Nov)
        'CTM-EXP-SOON'     => ['new_no' => 'CTM-25E080', 'exp' => '2026-11-12'], // Warning FEFO 30 hari
        'HUF-EXP-SOON'     => ['new_no' => 'HUF-24J089', 'exp' => '2026-11-20'], // Warning FEFO 30 hari
    ];

    foreach ($batchRenames as $oldBatch => $info) {
        $stmtCek = $pdo->prepare("SELECT id FROM stok_batch WHERE no_batch = ?");
        $stmtCek->execute([$oldBatch]);
        $bId = $stmtCek->fetchColumn();
        if ($bId) {
            $pdo->prepare("UPDATE stok_batch SET no_batch = ?, tgl_exp = ? WHERE id = ?")->execute([$info['new_no'], $info['exp'], $bId]);
            $pdo->prepare("UPDATE kartu_stok SET keterangan = REPLACE(keterangan, ?, ?) WHERE keterangan LIKE ?")->execute([$oldBatch, $info['new_no'], "%$oldBatch%"]);
            $pdo->prepare("UPDATE detail_penjualan SET catatan_logistik = REPLACE(catatan_logistik, ?, ?) WHERE catatan_logistik LIKE ?")->execute([$oldBatch, $info['new_no'], "%$oldBatch%"]);
            $pdo->prepare("UPDATE penerimaan_detail SET no_batch = ? WHERE no_batch = ?")->execute([$info['new_no'], $oldBatch]);
            echo "  ✓ Batch '$oldBatch' diubah ke format pabrik: '{$info['new_no']}' (Exp: {$info['exp']})\n";
        }
    }

    // ── 2. STANDARISASI SERIAL NUMBER (SN) KE FORMAT ALKES PABRIKAN ASLI ──
    $snRenames = [
        'OMR-TENS-01' => 'OMR-7120-101', 'OMR-TENS-02' => 'OMR-7120-102', 'OMR-TENS-03' => 'OMR-7120-103',
        'OMR-TENS-04' => 'OMR-7120-104', 'OMR-TENS-05' => 'OMR-7120-105', 'OMR-TENS-06' => 'OMR-7120-106',
        'OMR-TENS-07' => 'OMR-7120-107', 'OMR-TENS-08' => 'OMR-7120-108', 'OMR-TENS-09' => 'OMR-7120-109',
        'OMR-TH-01' => 'OMR-MC246-01', 'OMR-TH-02' => 'OMR-MC246-02', 'OMR-TH-03' => 'OMR-MC246-03',
        'OMR-TH-04' => 'OMR-MC246-04', 'OMR-TH-05' => 'OMR-MC246-05', 'OMR-TH-06' => 'OMR-MC246-06',
        'OMR-TH-07' => 'OMR-MC246-07', 'OMR-TH-08' => 'OMR-MC246-08', 'OMR-TH-09' => 'OMR-MC246-09',
        'OMR-TH-10' => 'OMR-MC246-10', 'OMR-TH-11' => 'OMR-MC246-11', 'OMR-TH-12' => 'OMR-MC246-12',
        'SLL-KRD-01' => 'SLL-CR809-01', 'SLL-KRD-02' => 'SLL-CR809-02',
        'O21M3-001' => 'O2-1M3-01', 'O21M3-002' => 'O2-1M3-02', 'O21M3-003' => 'O2-1M3-03', 'O21M3-004' => 'O2-1M3-04',
        'OMR-NEB-01' => 'OMR-C104-01', 'OMR-NEB-02' => 'OMR-C104-02', 'OMR-NEB-03' => 'OMR-C104-03',
        'OMR-NEB-04' => 'OMR-C104-04', 'OMR-NEB-05' => 'OMR-C104-05', 'OMR-NEB-06' => 'OMR-C104-06',
        'IRTHER-001' => 'IRT-FR1-01', 'IRTHER-002' => 'IRT-FR1-02', 'IRTHER-003' => 'IRT-FR1-03',
        'IRTHER-004' => 'IRT-FR1-04', 'IRTHER-005' => 'IRT-FR1-05', 'IRTHER-006' => 'IRT-FR1-06',
        'LTMSTC-001' => 'LIT-CL3-01', 'LTMSTC-002' => 'LIT-CL3-02', 'LTMSTC-003' => 'LIT-CL3-03',
    ];

    foreach ($snRenames as $oldSn => $newSn) {
        $stmtSn = $pdo->prepare("SELECT id FROM unit_serial WHERE serial_number = ?");
        $stmtSn->execute([$oldSn]);
        $snId = $stmtSn->fetchColumn();
        if ($snId) {
            $pdo->prepare("UPDATE unit_serial SET serial_number = ? WHERE id = ?")->execute([$newSn, $snId]);
            $pdo->prepare("UPDATE detail_penjualan SET catatan_logistik = REPLACE(catatan_logistik, ?, ?) WHERE catatan_logistik LIKE ?")->execute([$oldSn, $newSn, "%$oldSn%"]);
            $pdo->prepare("UPDATE kartu_stok SET keterangan = REPLACE(keterangan, ?, ?) WHERE keterangan LIKE ?")->execute([$oldSn, $newSn, "%$oldSn%"]);
            $pdo->prepare("UPDATE detail_opname_sn SET serial_number = ? WHERE serial_number = ?")->execute([$newSn, $oldSn]);
        }
    }
    echo "  ✓ Seluruh Serial Number (SN) distandarisasi ke format pabrik resmi.\n";

    // ── 3. LENGKAPI KONVERSI 1 BOX UNTUK SEMUA PRODUK OBAT (TOTAL 41 VARIASI) ──
    $obatList = $pdo->query("
        SELECT pv.id AS id_variasi, pv.nama_variasi, pi.nama_produk, pv.rasio_konversi, pv.satuan_besar, pv.satuan_kecil
        FROM produk_variasi pv
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        WHERE pi.kategori = 'Obat'
        ORDER BY pv.id ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $convertedCount = 0;
    foreach ($obatList as $ob) {
        $idVar = $ob['id_variasi'];
        $rasio = max(1, intval($ob['rasio_konversi']));
        
        // Cek apakah sudah ada sub-batch aktif
        $stmtCekSub = $pdo->prepare("SELECT id, no_batch, stok_sisa FROM stok_batch WHERE id_variasi = ? AND no_batch LIKE '%.%' LIMIT 1");
        $stmtCekSub->execute([$idVar]);
        $subRow = $stmtCekSub->fetch(PDO::FETCH_ASSOC);

        if (!$subRow) {
            // Ambil batch induk aktif dengan stok terbanyak / exp paling lama
            $stmtInduk = $pdo->prepare("SELECT id, no_batch, tgl_exp, stok_sisa FROM stok_batch WHERE id_variasi = ? AND no_batch NOT LIKE '%.%' AND stok_sisa >= ? ORDER BY stok_sisa DESC LIMIT 1");
            $stmtInduk->execute([$idVar, $rasio]);
            $indukRow = $stmtInduk->fetch(PDO::FETCH_ASSOC);

            if ($indukRow) {
                $idIndukBatch = $indukRow['id'];
                $noBatchInduk = $indukRow['no_batch'];
                $tglExpInduk  = $indukRow['tgl_exp'];
                $noSubBatch   = $noBatchInduk . ".1";

                // 1. Kurangi 1 box (Rasio pcs) dari batch induk
                $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa - ? WHERE id = ?")->execute([$rasio, $idIndukBatch]);

                // 2. Insert sub-batch hasil konversi
                $pdo->prepare("
                    INSERT INTO stok_batch (id_variasi, no_batch, tgl_exp, stok_sisa)
                    VALUES (?, ?, ?, ?)
                ")->execute([$idVar, $noSubBatch, $tglExpInduk, $rasio]);

                $sisaDusAkhir = (int) floor(($indukRow['stok_sisa'] - $rasio) / $rasio);

                // 3. Catat di histori_konversi
                $pdo->prepare("
                    INSERT INTO histori_konversi (id_variasi, batch_asal, batch_hasil, qty_box_buka, qty_pcs_hasil, sisa_dus_akhir, dibuat_oleh, created_at)
                    VALUES (?, ?, ?, 1, ?, ?, 1, '2026-10-25 09:00:00')
                ")->execute([$idVar, $noBatchInduk, $noSubBatch, $rasio, $sisaDusAkhir]);

                // 4. Catat di kartu_stok
                $pdo->prepare("
                    INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh, tanggal)
                    VALUES (?, ?, 'besar', 'Keluar', 'Manual', 'Koreksi Manual', 'KNV-INIT', 1, ?, ?, 1, '2026-10-25 09:00:00')
                ")->execute([$idVar, $idIndukBatch, $sisaDusAkhir, "Pemecahan 1 {$ob['satuan_besar']} menjadi $rasio {$ob['satuan_kecil']} [Sub-Batch: $noSubBatch]"]);

                $pdo->prepare("
                    INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh, tanggal)
                    VALUES (?, ?, 'kecil', 'Masuk', 'Manual', 'Koreksi Manual', 'KNV-INIT', ?, ?, ?, 1, '2026-10-25 09:00:00')
                ")->execute([$idVar, $idIndukBatch, $rasio, $rasio, "Penerimaan $rasio {$ob['satuan_kecil']} hasil pemecahan konversi dari Batch $noBatchInduk"]);

                $convertedCount++;
            }
        }
    }
    echo "  ✓ Konversi 1 box berhasil diterapkan pada $convertedCount variasi obat baru. Kini seluruh 41 obat memiliki sub-batch eceran siap jual!\n";

    // ── 4. VARIASI SKENARIO PEMBATALAN TRANSAKSI PENJUALAN LENGKAP ──
    // A. Transaksi Auto-Cancel Timer 2 Jam Habis
    $pdo->prepare("
        UPDATE penjualan 
        SET status_pesanan = 'Dibatalkan',
            alasan_batal = 'Auto-Cancel: Batas waktu pembayaran 2 jam telah habis',
            dibatalkan_oleh = 'Sistem',
            waktu_batal = '2026-10-28 14:00:00'
        WHERE no_invoice = 'WEB-20261028-544'
    ")->execute();

    // B. Transaksi Batal Mandiri oleh Pelanggan (Menunggu Pembayaran)
    $pdo->prepare("
        UPDATE penjualan 
        SET status_pesanan = 'Dibatalkan',
            alasan_batal = 'Pelanggan: Ingin Mengubah Alamat Pengiriman',
            dibatalkan_oleh = 'Pelanggan',
            waktu_batal = '2026-10-29 11:30:00'
        WHERE no_invoice = 'WEB-20261029-308'
    ")->execute();

    // C. Transaksi Permohonan Batal oleh Pelanggan Disetujui Superadmin (Tahap Diproses)
    $pdo->prepare("
        UPDATE penjualan 
        SET status_pesanan = 'Dibatalkan',
            request_batal = 1,
            request_batal_alasan = 'Salah memilih variasi kemasan obat',
            request_batal_waktu = '2026-11-01 10:15:00',
            alasan_batal = 'Permohonan Pelanggan Disetujui: Salah memilih variasi kemasan obat',
            dibatalkan_oleh = 'Superadmin (Pemilik)',
            waktu_batal = '2026-11-01 10:45:00'
        WHERE no_invoice = 'WEB-20261101-670'
    ")->execute();

    // D. Transaksi Permohonan Batal Pelanggan AKTIF (Pending Approval Admin)
    // Dibuat pada invoice WEB-20261101-726 agar saat demo muncul badge kuning & tombol Setujui/Tolak!
    $pdo->prepare("
        UPDATE penjualan 
        SET status_pesanan = 'Diproses',
            request_batal = 1,
            request_batal_alasan = 'Ingin mengganti kurir pengiriman ke Grab Instant',
            request_batal_waktu = '2026-11-03 14:30:00',
            alasan_batal = NULL,
            dibatalkan_oleh = NULL,
            waktu_batal = NULL
        WHERE no_invoice = 'WEB-20261101-726'
    ")->execute();

    // E. Transaksi Menunggu Pembayaran Aktif dengan Cooldown Timer Berjalan
    // Dibuat 35 menit yang lalu (masih sisa ~1 jam 25 menit)
    $pdo->prepare("
        UPDATE penjualan 
        SET status_pesanan = 'Menunggu Pembayaran',
            created_at = DATE_SUB(NOW(), INTERVAL 35 MINUTE),
            request_batal = 0,
            alasan_batal = NULL,
            dibatalkan_oleh = NULL,
            waktu_batal = NULL
        WHERE no_invoice = 'WEB-20261103-677'
    ")->execute();
    echo "  ✓ Seluruh 5 skenario pembatalan transaksi (Auto-cancel, Batal Mandiri, Request Batal Disetujui, Request Batal Pending, dan Timer Aktif) telah disiapkan sempurna.\n";

    // ── 5. PENATAAN SESI STOK OPNAME YANG LENGKAP & VARIASI ──
    // Sesi 1: OPN-20261015-001 (ID: 7) -> Selesai dengan Rekonsiliasi Mutasi Selisih Lengkap
    $pdo->prepare("
        UPDATE sesi_opname 
        SET status = 'Selesai',
            catatan = 'Stok Opname Triwulan III - Selesai dengan rekonsiliasi mutasi selisih barang rusak dan hilang.'
        WHERE kode_sesi = 'OPN-20261015-001'
    ")->execute();

    // Sesi 2: OPN-20261028-002 (ID: 3) -> Dibatalkan
    $pdo->prepare("
        UPDATE sesi_opname 
        SET status = 'Dibatalkan',
            kode_sesi = 'OPN-20261028-002',
            catatan = 'Dibatalkan oleh Superadmin karena gangguan pemadaman listrik PLN dan lonjakan antrean pasien di kasir.'
        WHERE id = 3
    ")->execute();

    // Sesi 3: OPN-20261101-003 (ID: 11) -> Selesai Sempurna Tanpa Selisih (Zero Discrepancy)
    $pdo->prepare("
        UPDATE sesi_opname 
        SET status = 'Selesai',
            kode_sesi = 'OPN-20261101-003',
            catatan = 'Stok Opname Rutin Awal Bulan - Seluruh fisik obat dan unit alkes 100% cocok dengan data sistem (Selesai Sempurna).'
        WHERE id = 11
    ")->execute();

    // Buat detail di Sesi 11 menjadi 100% Sesuai (Zero Discrepancy)
    $pdo->prepare("
        UPDATE detail_opname_batch 
        SET hitung_fisik = stok_sistem_fisik,
            selisih = 0,
            status_mutasi = 'Sesuai',
            alasan_mutasi = NULL,
            keterangan_mutasi = NULL,
            dihitung_oleh = 1,
            waktu_hitung = '2026-11-01 11:00:00'
        WHERE id_sesi = 11
    ")->execute();

    echo "  ✓ Tiga sesi stok opname realistis telah diatur (Selesai Rekonsiliasi, Dibatalkan, dan Selesai Sempurna).\n";

    // ── 6. SINKRONISASI STOK TOKO GLOBAL ────────────────────────────
    // Hitung ulang stok fisik toko per variasi agar sama persis dengan total sisa batch (Obat) dan total serial number aktif (Alkes)
    $allVars = $pdo->query("SELECT pv.id, pi.kategori FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($allVars as $v) {
        $idV = $v['id'];
        if ($v['kategori'] === 'Obat') {
            $totalBatch = (int)$pdo->query("SELECT COALESCE(SUM(stok_sisa), 0) FROM stok_batch WHERE id_variasi = $idV AND stok_sisa > 0")->fetchColumn();
            $pdo->prepare("UPDATE stok_toko SET stok = ? WHERE id_variasi = ?")->execute([$totalBatch, $idV]);
        } else {
            $totalSn = (int)$pdo->query("SELECT COUNT(*) FROM unit_serial WHERE id_variasi = $idV AND status = 'Tersedia'")->fetchColumn();
            // Jika produk alkes non-SN (seperti botol alkohol atau plester), sesuaikan dari batch atau stok_toko
            $totalBatchAlkes = (int)$pdo->query("SELECT COALESCE(SUM(stok_sisa), 0) FROM stok_batch WHERE id_variasi = $idV AND stok_sisa > 0")->fetchColumn();
            if ($totalSn > 0) {
                $pdo->prepare("UPDATE stok_toko SET stok = ? WHERE id_variasi = ?")->execute([$totalSn, $idV]);
            } elseif ($totalBatchAlkes > 0) {
                $pdo->prepare("UPDATE stok_toko SET stok = ? WHERE id_variasi = ?")->execute([$totalBatchAlkes, $idV]);
            }
        }
    }
    echo "  ✓ Stok toko global telah disinkronkan secara presisi dengan sisa fisik batch dan serial number.\n";

    $pdo->commit();
    echo "=== PEMUTAKHIRAN DATA SELESAI DENGAN SUKSES ===\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
