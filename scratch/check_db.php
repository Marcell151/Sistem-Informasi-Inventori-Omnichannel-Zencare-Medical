<?php
require __DIR__ . '/../config/koneksi.php';
$q = $pdo->query('SELECT v.id, v.sku_variasi, pi.nama_produk, v.nama_variasi, v.is_active, pi.kategori FROM produk_variasi v JOIN produk_induk pi ON v.id_produk_induk = pi.id');
foreach ($q as $r) {
    echo "{$r['id']}: {$r['sku_variasi']} - {$r['nama_produk']} ({$r['nama_variasi']}) [kat: {$r['kategori']}, active: {$r['is_active']}]\n";
}
