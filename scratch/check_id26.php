<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();
$s = $pdo->query('SELECT * FROM stok_toko WHERE id_variasi = 26')->fetch();
print_r($s);

