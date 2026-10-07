<?php
require_once __DIR__ . '/config/koneksi.php';
try {
    $pdo->exec("ALTER TABLE produk_variasi CHANGE stok_minimum stok_minimum_kecil INT DEFAULT 0");
    $pdo->exec("ALTER TABLE produk_variasi ADD COLUMN stok_minimum_besar INT DEFAULT 0 AFTER stok_minimum_kecil");
    echo "Success altering produk_variasi table\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), 'Unknown column') !== false) {
        echo "Column already exists or migrated\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
