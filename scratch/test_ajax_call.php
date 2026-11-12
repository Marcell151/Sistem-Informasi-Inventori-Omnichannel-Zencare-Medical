<?php
session_start();
$_SESSION['user_id'] = 4;
$_SESSION['role'] = 'kasir';
$_SESSION['nama_lengkap'] = 'Kasir (Front Office)';

$_GET['ajax'] = 'get_demo_counts';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$output = ob_get_clean();

$json = json_decode($output, true);
if (!$json) {
    echo "ERROR: Invalid JSON!\n";
    echo substr($output, 0, 500) . "\n";
    exit(1);
}

echo "Status: " . ($json['status'] ?? 'unknown') . "\n";
echo "Total data items: " . count($json['data'] ?? []) . "\n";

$anomalies = array_filter($json['data'], function($x) { return !empty($x['anomali']); });
echo "Total anomalies: " . count($anomalies) . "\n";
foreach ($anomalies as $a) {
    echo " - ID: {$a['id']}, Jenis: {$a['jenis']}, Baik: {$a['baik']}, Rusak: {$a['rusak']}, Exp: {$a['exp']}\n";
}

