<?php
$apiKey = 'gnnwdxZQc54b9d99b234c49aPqNcit3f';
$url = 'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost';

// Let's test Medan (id: 361)
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'origin' => '256', // Kota Malang
        'destination' => '361', // Medan, Sumut
        'weight' => 1000,
        'courier' => 'jne'
    ]),
    CURLOPT_HTTPHEADER => [
        'key: ' . $apiKey
    ],
    CURLOPT_TIMEOUT => 10
]);
$res = curl_exec($curl);
curl_close($curl);

echo "Medan JNE response: " . $res . "\n\n";

// Let's also check if any cache files have ctcjne or 14000

