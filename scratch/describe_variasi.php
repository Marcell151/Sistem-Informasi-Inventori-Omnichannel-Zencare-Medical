<?php
require_once __DIR__ . '/../config/koneksi.php';

$cols = $pdo->query("DESCRIBE produk_variasi")->fetchAll(PDO::FETCH_ASSOC);
echo "=== PRODUK VARIASI COLUMNS ===\n";
foreach ($cols as $c) {
    echo "- {$c['Field']} ({$c['Type']})\n";
}

$vars = $pdo->query("SELECT id, id_produk_induk, nama_variasi, sku_variasi, satuan_besar, satuan_kecil, rasio_konversi, stok_minimum_besar, stok_minimum_kecil, harga_jual_kecil, harga_jual_besar FROM produk_variasi ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
echo "\n=== VARIASI DATA ===\n";
foreach ($vars as $v) {
    echo "ID: {$v['id']} | Induk: {$v['id_produk_induk']} | {$v['nama_variasi']} [{$v['sku_variasi']}] | 1 {$v['satuan_besar']} = {$v['rasio_konversi']} {$v['satuan_kecil']} | Min B: {$v['stok_minimum_besar']}, Min K: {$v['stok_minimum_kecil']} | Kecil: {$v['harga_jual_kecil']}, Besar: {$v['harga_jual_besar']}\n";
}
