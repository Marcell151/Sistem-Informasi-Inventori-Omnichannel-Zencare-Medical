<?php
$apiKey = 'gnnwdxZQc54b9d99b234c49aPqNcit3f';
$url = 'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination?search=Muharto';
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['key: ' . $apiKey],
    CURLOPT_TIMEOUT => 10
]);
$res = curl_exec($curl);
curl_close($curl);
echo "Search Muharto: $res\n";

$url2 = 'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination?search=Kedungkandang';
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url2,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['key: ' . $apiKey],
    CURLOPT_TIMEOUT => 10
]);
$res2 = curl_exec($curl);
curl_close($curl);
echo "Search Kedungkandang: $res2\n";

