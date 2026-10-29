<?php
$apiKey = 'gnnwdxZQc54b9d99b234c49aPqNcit3f';
$url = 'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination?search=Nias%20Barat';
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['key: ' . $apiKey],
    CURLOPT_TIMEOUT => 10
]);
$res = curl_exec($curl);
curl_close($curl);
$data = json_decode($res, true);
echo "Nias Barat results:\n";
if (!empty($data['data'])) {
    foreach ($data['data'] as $d) {
        echo "ID: {$d['id']} | {$d['label']}\n";
    }
}

