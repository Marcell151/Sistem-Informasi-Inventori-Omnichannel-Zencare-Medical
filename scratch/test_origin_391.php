<?php
$apiKey = 'gnnwdxZQc54b9d99b234c49aPqNcit3f';
$url = 'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost';

// Let's test with origin: 391 (MALANG) to 361 (MEDAN)
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'origin' => '391', // Malang in Komerce
        'destination' => '361', // Medan in Komerce
        'weight' => 1000,
        'courier' => 'jne'
    ]),
    CURLOPT_HTTPHEADER => ['key: ' . $apiKey],
    CURLOPT_TIMEOUT => 10
]);
$res391 = curl_exec($curl);
curl_close($curl);
echo "Origin 391 (Malang) to 361 (Medan):\n$res391\n\n";

// Let's test with origin: 391 (MALANG) to 221 (Nias Barat)
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'origin' => '391', // Malang in Komerce
        'destination' => '221', // Nias Barat
        'weight' => 1000,
        'courier' => 'jne'
    ]),
    CURLOPT_HTTPHEADER => ['key: ' . $apiKey],
    CURLOPT_TIMEOUT => 10
]);
$resNias = curl_exec($curl);
curl_close($curl);
echo "Origin 391 (Malang) to 221 (Nias Barat):\n$resNias\n\n";

