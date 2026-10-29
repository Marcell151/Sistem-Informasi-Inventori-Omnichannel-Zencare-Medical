<?php
// Test cost calculation through api/rajaongkir.php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['action'] = 'cost';

// 1. Test destination: MEDAN
$_POST['origin'] = '256';
$_POST['destination'] = '361';
$_POST['city_name'] = 'KOTA MEDAN';
$_POST['weight'] = 1000;
$_POST['courier'] = 'jne';

ob_start();
include 'api/rajaongkir.php';
$outMedan = ob_get_clean();

echo "=== RESULT FOR KOTA MEDAN ===\n";
$dataMedan = json_decode($outMedan, true);
if (!empty($dataMedan['data'])) {
    foreach ($dataMedan['data'] as $s) {
        echo "   [{$s['service']}] {$s['description']}: Rp " . number_format($s['cost']) . " (ETD: {$s['etd']})\n";
    }
} else {
    echo "Raw: " . substr($outMedan, 0, 300) . "\n";
}

