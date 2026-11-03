<?php
require __DIR__ . '/../config/koneksi.php';

try {
    $cols = $pdo->query("SHOW COLUMNS FROM penjualan")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('alasan_batal', $cols)) {
        $pdo->exec("ALTER TABLE penjualan ADD COLUMN alasan_batal VARCHAR(255) NULL AFTER status_pesanan");
        echo "[OK] Added alasan_batal to penjualan\n";
    } else {
        echo "[EXISTS] alasan_batal already present\n";
    }

    if (!in_array('dibatalkan_oleh', $cols)) {
        $pdo->exec("ALTER TABLE penjualan ADD COLUMN dibatalkan_oleh VARCHAR(50) NULL AFTER alasan_batal");
        echo "[OK] Added dibatalkan_oleh to penjualan\n";
    } else {
        echo "[EXISTS] dibatalkan_oleh already present\n";
    }

    if (!in_array('waktu_batal', $cols)) {
        $pdo->exec("ALTER TABLE penjualan ADD COLUMN waktu_batal TIMESTAMP NULL AFTER dibatalkan_oleh");
        echo "[OK] Added waktu_batal to penjualan\n";
    } else {
        echo "[EXISTS] waktu_batal already present\n";
    }

    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}

