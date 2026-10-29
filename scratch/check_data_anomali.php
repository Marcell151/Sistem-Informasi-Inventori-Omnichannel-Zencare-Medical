<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();
$s = $pdo->query('SELECT * FROM log_anomali_fefo ORDER BY id DESC LIMIT 10');
print_r($s->fetchAll(PDO::FETCH_ASSOC));

