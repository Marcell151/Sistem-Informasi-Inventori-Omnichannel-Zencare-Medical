<?php
$ids = [
    25 => 'RIAU',
    5  => 'JAWA BARAT',
    10 => 'DKI JAKARTA',
    15 => 'BALI',
    16 => 'SUMATERA UTARA',
    18 => 'JAWA TIMUR',
    8  => 'KEPULAUAN RIAU',
    9  => 'ACEH',
    14 => 'PAPUA'
];

foreach ($ids as $id => $name) {
    $url = 'http://localhost/inventory_zencare/api/rajaongkir.php?action=cities&province_id=' . $id;
    $content = file_get_contents($url);
    $res = json_decode($content, true);
    $c = $res['data'] ?? [];
    echo sprintf("%-15s (ID %2d): %2d kota | Contoh: %s\n", $name, $id, count($c), ($c[0]['name'] ?? 'KOSONG'));
}

