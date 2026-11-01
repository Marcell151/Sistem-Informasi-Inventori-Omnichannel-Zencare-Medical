<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$res = $pdo->query("SELECT id, sku_induk, nama_produk, is_active FROM produk_induk ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
print_r($res);

