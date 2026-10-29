<?php
require 'api/indonesia_regions.php';

$testProvinces = [
    25 => 'RIAU',
    10 => 'DKI JAKARTA',
    5  => 'JAWA BARAT',
    15 => 'BALI',
    16 => 'SUMATERA UTARA',
    18 => 'JAWA TIMUR',
    8  => 'KEPULAUAN RIAU'
];

foreach ($testProvinces as $pId => $pName) {
    $_GET['action'] = 'cities';
    $_GET['province_id'] = (string)$pId;
    ob_start();
    include 'api/rajaongkir.php';
    $out = ob_get_clean();
    $data = json_decode($out, true);
    $cities = $data['data'] ?? [];
    echo "Province [$pId: $pName] -> " . count($cities) . " cities loaded\n";
    if (count($cities) > 0) {
        $sample = array_slice($cities, 0, 3);
        $names = array_map(function($c) { return $c['name']; }, $sample);
        echo "   Sample: " . implode(', ', $names) . "\n";
    } else {
        echo "   [ERROR] No cities!\n";
    }
}

