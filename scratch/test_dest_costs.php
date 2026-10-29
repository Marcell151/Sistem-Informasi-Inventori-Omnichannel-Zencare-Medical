<?php
$apiKey = 'gnnwdxZQc54b9d99b234c49aPqNcit3f';
$targets = [
    'Medan' => 41068,
    'Nias Barat' => 27458,
    'Surabaya' => 74945,
    'Jakarta Pusat' => 17596
];

foreach ($targets as $name => $destId) {
    $url = 'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost';
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'origin' => '46748', // Malang
            'destination' => $destId,
            'weight' => 1000,
            'courier' => 'jne'
        ]),
        CURLOPT_HTTPHEADER => ['key: ' . $apiKey],
        CURLOPT_TIMEOUT => 10
    ]);
    $res = json_decode(curl_exec($curl), true);
    curl_close($curl);
    
    echo "Malang -> $name (Dest ID $destId):\n";
    if (!empty($res['data'])) {
        foreach ($res['data'] as $s) {
            echo "   [{$s['service']}] {$s['description']}: Rp " . number_format($s['cost']) . " (ETD: {$s['etd']})\n";
        }
    } else {
        echo "   Error/Empty: " . ($res['meta']['message'] ?? 'No data') . "\n";
    }
}

