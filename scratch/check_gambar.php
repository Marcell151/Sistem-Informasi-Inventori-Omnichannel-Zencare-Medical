<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query('SELECT id, nama_variasi, gambar FROM produk_variasi LIMIT 10');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "ID: {$r['id']} | Variasi: {$r['nama_variasi']} | Gambar: {$r['gambar']}\n";
}

