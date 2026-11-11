<?php
require __DIR__ . '/../config/db.php';
$pdo = DB::connect();

try {
    // Check if columns exist in detail_opname_batch
    $cols = $pdo->query("SHOW COLUMNS FROM detail_opname_batch LIKE 'fisik_baik'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("
            ALTER TABLE detail_opname_batch
            ADD COLUMN fisik_baik int(11) DEFAULT NULL AFTER stok_sistem_fisik,
            ADD COLUMN fisik_rusak int(11) DEFAULT 0 AFTER fisik_baik,
            ADD COLUMN fisik_expired int(11) DEFAULT 0 AFTER fisik_rusak,
            ADD COLUMN status_hitung enum('Belum','Sudah') DEFAULT 'Belum' AFTER fisik_expired
        ");
        echo "✓ Added fisik_baik, fisik_rusak, fisik_expired, status_hitung to detail_opname_batch\n";
    } else {
        echo "detail_opname_batch already has fisik_baik\n";
    }

    // Check if columns exist in sesi_opname
    $colsSesi = $pdo->query("SHOW COLUMNS FROM sesi_opname LIKE 'tahap'")->fetchAll();
    if (empty($colsSesi)) {
        $pdo->exec("
            ALTER TABLE sesi_opname
            ADD COLUMN tahap enum('Penghitungan','Verifikasi','Selesai') DEFAULT 'Penghitungan' AFTER status,
            ADD COLUMN ditutup_hitung_oleh int(11) DEFAULT NULL AFTER dibekukan_pada,
            ADD COLUMN ditutup_hitung_pada timestamp NULL DEFAULT NULL AFTER ditutup_hitung_oleh
        ");
        echo "✓ Added tahap, ditutup_hitung_oleh, ditutup_hitung_pada to sesi_opname\n";
    } else {
        echo "sesi_opname already has tahap\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

