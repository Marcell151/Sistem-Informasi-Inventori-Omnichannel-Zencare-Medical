<?php
require_once __DIR__ . '/../config/koneksi.php';
$d = $pdo->query("SELECT * FROM detail_penjualan WHERE id_penjualan = 44")->fetchAll(PDO::FETCH_ASSOC);
print_r($d);

