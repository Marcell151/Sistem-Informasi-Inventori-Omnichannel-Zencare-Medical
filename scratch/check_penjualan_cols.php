<?php
require __DIR__ . '/../config/koneksi.php';
$cols = $pdo->query("SHOW COLUMNS FROM penjualan")->fetchAll(PDO::FETCH_COLUMN);
print_r($cols);
