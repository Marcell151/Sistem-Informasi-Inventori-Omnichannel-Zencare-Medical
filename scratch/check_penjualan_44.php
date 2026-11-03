<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query('SELECT dp.*, pv.nama_variasi, pi.nama_produk FROM detail_penjualan dp JOIN produk_variasi pv ON dp.id_variasi = pv.id JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE dp.id_penjualan = 44');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

