<?php
require __DIR__ . '/../config/koneksi.php';

try {
    $cols = $pdo->query("SHOW COLUMNS FROM penjualan")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('request_batal', $cols)) {
        $pdo->exec("ALTER TABLE penjualan ADD COLUMN request_batal TINYINT(1) NOT NULL DEFAULT 0 AFTER waktu_batal");
        echo "[OK] Added request_batal to penjualan\n";
    } else {
        echo "[EXISTS] request_batal already present\n";
    }

    if (!in_array('request_batal_alasan', $cols)) {
        $pdo->exec("ALTER TABLE penjualan ADD COLUMN request_batal_alasan VARCHAR(255) NULL AFTER request_batal");
        echo "[OK] Added request_batal_alasan to penjualan\n";
    } else {
        echo "[EXISTS] request_batal_alasan already present\n";
    }

    if (!in_array('request_batal_waktu', $cols)) {
        $pdo->exec("ALTER TABLE penjualan ADD COLUMN request_batal_waktu TIMESTAMP NULL AFTER request_batal_alasan");
        echo "[OK] Added request_batal_waktu to penjualan\n";
    } else {
        echo "[EXISTS] request_batal_waktu already present\n";
    }

    echo "Migration for request_batal completed.\n";
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}

