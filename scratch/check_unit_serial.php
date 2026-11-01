<?php
require_once __DIR__ . '/../config/koneksi.php';

$res = $pdo->query("SELECT id_variasi, serial_number, status FROM unit_serial ORDER BY id_variasi, id ASC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
