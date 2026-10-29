<?php
require_once 'config/koneksi.php';
$stmt = $pdo->query("SELECT v.id, v.sku_variasi, v.nama_variasi, v.rasio_konversi, pi.kategori 
FROM produk_variasi v 
JOIN produk_induk pi ON v.id_produk_induk = pi.id 
WHERE pi.kategori = 'Obat' AND v.rasio_konversi = 1");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
