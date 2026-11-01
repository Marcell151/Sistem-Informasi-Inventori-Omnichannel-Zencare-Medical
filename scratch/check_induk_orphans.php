<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$res = $pdo->query('SELECT * FROM produk_induk WHERE id IN (58,59,60)')->fetchAll(PDO::FETCH_ASSOC);
print_r($res);

