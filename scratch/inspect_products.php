<?php
require_once __DIR__ . '/../config/koneksi.php';

echo "=== PRODUK INDUK ===\n";
$pi = $pdo->query("SELECT id, sku_induk, nama_produk, kategori FROM produk_induk ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($pi as $p) {
    echo "ID: {$p['id']} | [{$p['sku_induk']}] {$p['nama_produk']} ({$p['kategori']})\n";
}

echo "\n=== PRODUK VARIASI ===\n";
$pv = $pdo->query("
    SELECT pv.id, pv.id_produk_induk, pi.nama_produk, pv.nama_variasi, pv.sku_variasi,
           pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi, pv.stok_minimum_besar, pv.stok_minimum_kecil,
           pv.harga_beli, pv.harga_jual, pv.harga_grosir, pi.kategori
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    ORDER BY pv.id
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($pv as $v) {
    echo "ID: {$v['id']} (Induk: {$v['id_produk_induk']}) | {$v['nama_produk']} - {$v['nama_variasi']} [SKU: {$v['sku_variasi']}] | 1 {$v['satuan_besar']} = {$v['rasio_konversi']} {$v['satuan_kecil']} | Min B: {$v['stok_minimum_besar']}, Min K: {$v['stok_minimum_kecil']}\n";
}
