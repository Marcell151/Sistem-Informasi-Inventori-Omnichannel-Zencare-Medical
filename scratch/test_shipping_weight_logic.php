<?php
require __DIR__ . '/../api/indonesia_regions.php';

echo "=== TEST 1: KOTA SURABAYA (JAWA TIMUR) ===\n";
echo "1A. Paket Ringan (200 Gram):\n";
$res1 = json_decode(getFallbackShippingCost('KOTA SURABAYA', 200, 'jne'), true);
foreach ($res1['data'] as $s) {
    echo "  - {$s['service']} ({$s['description']}): Rp " . number_format($s['cost'], 0, ',', '.') . " [{$s['etd']}]\n";
}

echo "\n1B. Paket Berat / Kursi Roda (15.000 Gram / 15 Kg):\n";
$res2 = json_decode(getFallbackShippingCost('KOTA SURABAYA', 15000, 'jne'), true);
foreach ($res2['data'] as $s) {
    echo "  - {$s['service']} ({$s['description']}): Rp " . number_format($s['cost'], 0, ',', '.') . " [{$s['etd']}]\n";
}

echo "\n=== TEST 2: KOTA MEDAN (SUMATERA UTARA - LUAR JAWA) ===\n";
echo "2A. Paket Ringan (500 Gram):\n";
$res3 = json_decode(getFallbackShippingCost('KOTA MEDAN', 500, 'jne'), true);
foreach ($res3['data'] as $s) {
    echo "  - {$s['service']} ({$s['description']}): Rp " . number_format($s['cost'], 0, ',', '.') . " [{$s['etd']}]\n";
}

echo "\n2B. Paket Berat / Kursi Roda (15.000 Gram / 15 Kg):\n";
$res4 = json_decode(getFallbackShippingCost('KOTA MEDAN', 15000, 'jne'), true);
foreach ($res4['data'] as $s) {
    echo "  - {$s['service']} ({$s['description']}): Rp " . number_format($s['cost'], 0, ',', '.') . " [{$s['etd']}]\n";
}

