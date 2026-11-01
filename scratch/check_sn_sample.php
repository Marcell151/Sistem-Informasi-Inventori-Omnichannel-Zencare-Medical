<?php
require_once __DIR__ . '/../config/koneksi.php';
$d = $pdo->query("SELECT * FROM detail_penjualan WHERE catatan_logistik LIKE '%SN%' OR catatan_logistik LIKE '%SLL%' LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($d);

