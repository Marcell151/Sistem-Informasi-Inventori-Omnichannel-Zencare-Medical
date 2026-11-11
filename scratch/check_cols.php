<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
$cols = $pdo->query("DESCRIBE sesi_opname")->fetchAll(PDO::FETCH_COLUMN);
print_r($cols);

