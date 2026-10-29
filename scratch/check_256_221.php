<?php
$apiKey = 'gnnwdxZQc54b9d99b234c49aPqNcit3f';

// Check what destination has ID 256 or ID 221
// Let's test calling /calculate/domestic-cost with origin 46748 (Malang) vs destination
$url = 'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost';
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'origin' => '256',
        'destination' => '256',
        'weight' => 1000,
        'courier' => 'jne'
    ]),
    CURLOPT_HTTPHEADER => ['key: ' . $apiKey],
    CURLOPT_TIMEOUT => 10
]);
$resSame = curl_exec($curl);
curl_close($curl);
echo "256 to 256:\n$resSame\n\n";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'origin' => '256',
        'destination' => '221',
        'weight' => 1000,
        'courier' => 'jne'
    ]),
    CURLOPT_HTTPHEADER => ['key: ' . $apiKey],
    CURLOPT_TIMEOUT => 10
]);
$res221 = curl_exec($curl);
curl_close($curl);
echo "256 to 221:\n$res221\n";

