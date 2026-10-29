<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['action'] = 'cost';

$_POST['origin'] = '256';
$_POST['destination'] = '201';
$_POST['city_name'] = 'KOTA PEKANBARU';
$_POST['weight'] = 1000;
$_POST['courier'] = 'jne';

ob_start();
include 'api/rajaongkir.php';
$outPku = ob_get_clean();

echo "=== COST RESULT FOR PEKANBARU, RIAU ===\n";
$dataPku = json_decode($outPku, true);
if (!empty($dataPku['data'])) {
    foreach ($dataPku['data'] as $s) {
        echo "   [{$s['service']}] {$s['description']}: Rp " . number_format($s['cost']) . " (ETD: {$s['etd']})\n";
    }
} else {
    echo "Raw: " . substr($outPku, 0, 300) . "\n";
}

