<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['action'] = 'cost';

// Test Surabaya
$_POST['origin'] = '256';
$_POST['destination'] = '577';
$_POST['city_name'] = 'KOTA SURABAYA';
$_POST['weight'] = 1000;
$_POST['courier'] = 'jne';

ob_start();
include 'api/rajaongkir.php';
$outSby = ob_get_clean();

echo "=== RESULT FOR KOTA SURABAYA ===\n";
$dataSby = json_decode($outSby, true);
if (!empty($dataSby['data'])) {
    foreach ($dataSby['data'] as $s) {
        echo "   [{$s['service']}] {$s['description']}: Rp " . number_format($s['cost']) . " (ETD: {$s['etd']})\n";
    }
}

