<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("
    SELECT pv.id, pi.id as id_induk, pi.nama_produk, pv.nama_variasi, pi.kategori, pv.sku_variasi 
    FROM produk_variasi pv 
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id 
    ORDER BY pv.id ASC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
file_put_contents(__DIR__ . '/products_list.json', json_encode($products, JSON_PRETTY_PRINT));
echo "Exported " . count($products) . " products to products_list.json\n";
