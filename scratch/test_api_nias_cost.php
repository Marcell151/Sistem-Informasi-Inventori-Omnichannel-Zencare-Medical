<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['action'] = 'cost';

// Test destination: NIAS BARAT
$_POST['origin'] = '256';
$_POST['destination'] = '221';
$_POST['city_name'] = 'KABUPATEN NIAS BARAT';
$_POST['weight'] = 1000;
$_POST['courier'] = 'jne';

ob_start();
include 'api/rajaongkir.php';
$outNias = ob_get_clean();

echo "=== RESULT FOR KABUPATEN NIAS BARAT ===\n";
$dataNias = json_decode($outNias, true);
if (!empty($dataNias['data'])) {
    foreach ($dataNias['data'] as $s) {
        echo "   [{$s['service']}] {$s['description']}: Rp " . number_format($s['cost']) . " (ETD: {$s['etd']})\n";
    }
} else {
    echo "Raw: " . substr($outNias, 0, 300) . "\n";
}

