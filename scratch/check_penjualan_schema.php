<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query('DESCRIBE penjualan');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$sample = $pdo->query("SELECT * FROM penjualan ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
print_r($sample);

