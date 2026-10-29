<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();
$s = $pdo->query('DESCRIBE detail_penjualan');
print_r($s->fetchAll(PDO::FETCH_ASSOC));

