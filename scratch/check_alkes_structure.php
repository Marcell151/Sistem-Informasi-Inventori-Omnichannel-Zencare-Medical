<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

echo "--- unit_serial for Tensimeter (var_id 4) ---" . PHP_EOL;
$sn4 = $pdo->query("SELECT * FROM unit_serial WHERE id_variasi = 4 LIMIT 5")->fetchAll();
foreach ($sn4 as $s) echo "SN: {$s['serial_number']} | Status: {$s['status']}" . PHP_EOL;

echo "--- unit_serial for EasyTouch (var_id 51) ---" . PHP_EOL;
$sn51 = $pdo->query("SELECT * FROM unit_serial WHERE id_variasi = 51 LIMIT 5")->fetchAll();
echo "Total SN for EasyTouch: " . count($sn51) . PHP_EOL;

echo "--- stok_batch for EasyTouch (var_id 51) ---" . PHP_EOL;
$b51 = $pdo->query("SELECT * FROM stok_batch WHERE id_variasi = 51 LIMIT 5")->fetchAll();
foreach ($b51 as $b) echo "Batch: {$b['no_batch']} | Sisa: {$b['stok_sisa']} | Exp: {$b['tgl_exp']}" . PHP_EOL;

echo "--- stok_batch for Tensimeter (var_id 4) ---" . PHP_EOL;
$b4 = $pdo->query("SELECT * FROM stok_batch WHERE id_variasi = 4 LIMIT 5")->fetchAll();
echo "Total stok_batch for Tensimeter: " . count($b4) . PHP_EOL;
