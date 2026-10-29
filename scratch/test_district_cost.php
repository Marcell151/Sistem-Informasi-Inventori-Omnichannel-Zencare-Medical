<?php
$apiKey = 'gnnwdxZQc54b9d99b234c49aPqNcit3f';

// Test endpoint /calculate/district/domestic-cost
$url = 'https://rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost';
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'origin' => '256',
        'destination' => '361',
        'weight' => 1000,
        'courier' => 'jne'
    ]),
    CURLOPT_HTTPHEADER => ['key: ' . $apiKey],
    CURLOPT_TIMEOUT => 10
]);
$res = curl_exec($curl);
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);
echo "District domestic cost (HTTP $code):\n$res\n";

