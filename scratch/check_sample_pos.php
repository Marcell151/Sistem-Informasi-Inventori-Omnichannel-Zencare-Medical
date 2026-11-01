<?php
require_once __DIR__ . '/../config/koneksi.php';
$pos = $pdo->query("SELECT * FROM penjualan WHERE tipe_transaksi = 'pos' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
print_r($pos);

