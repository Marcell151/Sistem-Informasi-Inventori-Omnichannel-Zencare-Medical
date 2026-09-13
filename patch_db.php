<?php
require 'config/config.php';
require 'config/koneksi.php';

try {
    $pdo->exec('ALTER TABLE penjualan ADD COLUMN metode_pembayaran VARCHAR(50) DEFAULT "Tunai" AFTER total_harga');
    echo 'Added column metode_pembayaran.';
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
