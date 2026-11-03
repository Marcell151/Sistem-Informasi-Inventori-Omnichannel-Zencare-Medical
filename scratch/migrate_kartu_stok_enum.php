<?php
require_once __DIR__ . '/../config/koneksi.php';

try {
    $pdo->exec("
        ALTER TABLE kartu_stok 
        MODIFY COLUMN alasan_mutasi ENUM(
            'Penerimaan Barang',
            'Penjualan POS',
            'Penjualan E-Commerce',
            'Retur Barang Rusak',
            'Klaim Garansi SN',
            'Selisih Stok Opname',
            'Barang Rusak',
            'Barang Kedaluwarsa',
            'Barang Hilang',
            'Koreksi Salah Catat / Ditemukan',
            'Koreksi Manual'
        ) NOT NULL DEFAULT 'Selisih Stok Opname'
    ");
    echo "SUCCESS: kartu_stok.alasan_mutasi enum successfully expanded.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

