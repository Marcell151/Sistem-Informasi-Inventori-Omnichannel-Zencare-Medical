<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query("SELECT pv.id, pi.nama_produk, pi.kategori FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE pi.kategori = 'Alat Kesehatan' LIMIT 5");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['id']} | {$r['nama_produk']} | {$r['kategori']}\n";
}

