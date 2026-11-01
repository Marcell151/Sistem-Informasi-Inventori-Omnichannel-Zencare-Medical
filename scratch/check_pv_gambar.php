<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$res = $pdo->query("SELECT id, nama_variasi, gambar FROM produk_variasi WHERE is_active=1 LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
print_r($res);

