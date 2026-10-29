<?php
$apiKey = 'gnnwdxZQc54b9d99b234c49aPqNcit3f';
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
        'courier' => 'jne'
    ]),
    CURLOPT_HTTPHEADER => [
        'key: ' . $apiKey
    ],
    CURLOPT_TIMEOUT => 10
]);
$res = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo "HTTP Code: $httpCode\n";
echo "Response: $res\n";
