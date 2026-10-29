<?php
$apiKey = 'gnnwdxZQc54b9d99b234c49aPqNcit3f';

// Test finding destination ID from city name or district
function getDestinationIdByCityName($cityName) {
    global $apiKey;
    $url = 'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination?search=' . urlencode(trim($cityName)) . '&limit=1';
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['key: ' . $apiKey],
        CURLOPT_TIMEOUT => 10
    ]);
    $res = json_decode(curl_exec($curl), true);
    curl_close($curl);
    return $res['data'][0]['id'] ?? null;
}

echo "Medan: " . getDestinationIdByCityName('MEDAN') . "\n";
echo "Nias Barat: " . getDestinationIdByCityName('NIAS BARAT') . "\n";
echo "Surabaya: " . getDestinationIdByCityName('SURABAYA') . "\n";
echo "Jakarta Pusat: " . getDestinationIdByCityName('JAKARTA PUSAT') . "\n";

