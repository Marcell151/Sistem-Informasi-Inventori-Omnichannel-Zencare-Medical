<?php
require_once 'config/koneksi.php';

echo "=== VARIATION 7 (HUFA BIRU) ===\n";
$stmt = $pdo->prepare("SELECT p.id, p.nama_produk, v.id as id_variasi, v.nama_variasi, v.rasio, v.satuan_kecil, v.satuan_besar
FROM variasi_produk v 
JOIN produk p ON v.id_produk = p.id 
WHERE v.nama_variasi LIKE '%Biru%' OR p.nama_produk LIKE '%Hufagrip%'");
$stmt->execute();
$vars = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($vars);

echo "=== STOK BATCH FOR THESE VARIANTS ===\n";
$stmt = $pdo->prepare("SELECT sb.*, v.nama_variasi 
FROM stok_batch sb 
JOIN variasi_produk v ON sb.id_variasi = v.id 
WHERE v.nama_variasi LIKE '%Biru%' OR sb.no_batch LIKE '%HUF%'");
$stmt->execute();
$batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($batches);
