<?php
$_GET['action'] = 'cities';
$_GET['province_id'] = '24'; // Jawa Timur
ob_start();
include 'api/rajaongkir.php';
$out = ob_get_clean();
echo "Cities response: " . substr($out, 0, 300) . "\n";

$_GET['action'] = 'cost';
$_POST['origin'] = '256';
$_POST['destination'] = '577';
$_POST['weight'] = '1000';
$_POST['courier'] = 'jne';
$_SERVER['REQUEST_METHOD'] = 'POST';
ob_start();
include 'api/rajaongkir.php';
$outCost = ob_get_clean();
echo "Cost response: " . substr($outCost, 0, 300) . "\n";

