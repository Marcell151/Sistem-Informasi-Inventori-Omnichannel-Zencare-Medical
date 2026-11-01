<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$res = $pdo->query("SELECT * FROM produk_variasi WHERE id IN (61, 62, 63)")->fetchAll(PDO::FETCH_ASSOC);
print_r($res);

