<?php
$apiKey = 'gnnwdxZQc54b9d99b234c49aPqNcit3f';

// Let's inspect /destination/city/16 (Sumatera Utara)
// and let's check /destination/domestic-destination?search=Medan
// and let's check /destination/domestic-destination?search=Malang

$url = 'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination?search=Medan';
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['key: ' . $apiKey],
    CURLOPT_TIMEOUT => 10
]);
$resMedan = curl_exec($curl);
curl_close($curl);
echo "Search Medan:\n" . substr($resMedan, 0, 500) . "\n\n";

$url = 'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination?search=Malang';
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['key: ' . $apiKey],
    CURLOPT_TIMEOUT => 10
]);
$resMalang = curl_exec($curl);
curl_close($curl);
echo "Search Malang:\n" . substr($resMalang, 0, 500) . "\n\n";

