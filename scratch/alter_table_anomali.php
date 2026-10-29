<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

try {
    // Check if columns exist
    $cols = $pdo->query("SHOW COLUMNS FROM log_anomali_fefo")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('satuan_tipe', $cols)) {
        $pdo->exec("ALTER TABLE log_anomali_fefo ADD COLUMN satuan_tipe VARCHAR(20) NOT NULL DEFAULT 'kecil' AFTER qty");
        echo "Added column satuan_tipe\n";
    }

    if (!in_array('status', $cols)) {
        $pdo->exec("ALTER TABLE log_anomali_fefo ADD COLUMN status ENUM('terlewat', 'disesuaikan') NOT NULL DEFAULT 'terlewat' AFTER satuan_tipe");
        echo "Added column status\n";
    }

    if (!in_array('resolved_in_penjualan', $cols)) {
        $pdo->exec("ALTER TABLE log_anomali_fefo ADD COLUMN resolved_in_penjualan INT(11) NULL AFTER status");
        echo "Added column resolved_in_penjualan\n";
    }

    if (!in_array('resolved_at', $cols)) {
        $pdo->exec("ALTER TABLE log_anomali_fefo ADD COLUMN resolved_at DATETIME NULL AFTER resolved_in_penjualan");
        echo "Added column resolved_at\n";
    }

    echo "Columns verified successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

