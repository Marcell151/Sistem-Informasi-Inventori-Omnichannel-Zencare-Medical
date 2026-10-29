<?php
$apiKey = 'gnnwdxZQc54b9d99b234c49aPqNcit3f';
$url = 'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination?search=Klojen'; // Malang city center
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['key: ' . $apiKey],
    CURLOPT_TIMEOUT => 10
]);
$res = curl_exec($curl);
curl_close($curl);
echo "Klojen (Malang):\n$res\n\n";

$urlMedan = 'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination?search=Medan%20Kota';
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $urlMedan,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['key: ' . $apiKey],
    CURLOPT_TIMEOUT => 10
]);
$resMedan = curl_exec($curl);
curl_close($curl);
echo "Medan Kota:\n$resMedan\n\n";
