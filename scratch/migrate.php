<?php
require_once __DIR__ . '/config/koneksi.php';
try {
    $pdo->exec("ALTER TABLE kartu_stok ADD COLUMN satuan_tipe ENUM('besar', 'kecil') NOT NULL DEFAULT 'kecil' AFTER id_variasi");
    echo "Success altering table";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
