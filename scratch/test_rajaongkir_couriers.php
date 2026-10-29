<?php
$apiKey = 'gnnwdxZQc54b9d99b234c49aPqNcit3f';
$couriers = ['jne', 'pos', 'tiki', 'sicepat', 'jnt', 'wahana', 'lion', 'ninja'];

foreach ($couriers as $courier) {
    $url = 'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost';
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'origin' => '256',
            'destination' => '577', // Surabaya
            'weight' => 1000,
            'courier' => $courier
        ]),
        CURLOPT_HTTPHEADER => [
            'key: ' . $apiKey
        ],
        CURLOPT_TIMEOUT => 10
    ]);
    $res = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    $data = json_decode($res, true);
    $status = $data['meta']['status'] ?? 'unknown';
    $count = isset($data['data']) && is_array($data['data']) ? count($data['data']) : 0;
    echo "Courier [$courier]: HTTP $httpCode | Status: $status | Services: $count\n";
    if ($count > 0) {
        foreach ($data['data'] as $s) {
            echo "   -> " . ($s['service'] ?? '-') . " (" . ($s['description'] ?? '-') . "): Rp " . number_format($s['cost'] ?? 0) . " | ETD: " . ($s['etd'] ?? '-') . "\n";
        }
    } else {
        echo "   -> Message: " . ($data['meta']['message'] ?? 'No data') . "\n";
    }
}

