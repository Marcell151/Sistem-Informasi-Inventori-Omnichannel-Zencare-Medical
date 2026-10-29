<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['action'] = 'cost';

foreach (['jne', 'pos', 'jnt'] as $courier) {
    $_POST['origin'] = '256';
    $_POST['destination'] = '201';
    $_POST['city_name'] = 'KOTA PEKANBARU';
    $_POST['weight'] = 1000;
    $_POST['courier'] = $courier;

    ob_start();
    include 'api/rajaongkir.php';
    $out = ob_get_clean();
    $d = json_decode($out, true);
    echo "Courier [$courier] -> " . count($d['data'] ?? []) . " services:\n";
    foreach ($d['data'] ?? [] as $s) {
        echo "   {$s['service']} ({$s['description']}): Rp " . number_format($s['cost']) . "\n";
    }
}

