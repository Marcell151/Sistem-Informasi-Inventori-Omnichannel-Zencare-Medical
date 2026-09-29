<?php
require_once __DIR__ . '/../config/koneksi.php';

try {
    // MODUL 1: Tambah batas_stok_minimum ke produk_variasi
    $sql1 = "ALTER TABLE produk_variasi ADD COLUMN stok_minimum INT DEFAULT 0 AFTER rasio_konversi";
    $pdo->exec($sql1);
    echo "Berhasil menambah kolom stok_minimum ke produk_variasi.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Kolom stok_minimum sudah ada.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

try {
    // MODUL 2: Tambah batas_hari_expired ke produk_induk (atau produk_variasi, sesuai TRD Modul 2 batas_hari_expired per obat)
    // Sesuai TRD, beda-beda per obat (Sirup vs Tablet), jadi kita masukkan ke produk_induk atau produk_variasi.
    // ZenCare usually puts global characteristics like category and generic limits in produk_induk.
    // Let's add to produk_induk.
    $sql2 = "ALTER TABLE produk_induk ADD COLUMN batas_hari_expired INT DEFAULT 0 AFTER is_active";
    $pdo->exec($sql2);
    echo "Berhasil menambah kolom batas_hari_expired ke produk_induk.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Kolom batas_hari_expired sudah ada di produk_induk.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

echo "\nUpdate Database Selesai!\n";
?>
