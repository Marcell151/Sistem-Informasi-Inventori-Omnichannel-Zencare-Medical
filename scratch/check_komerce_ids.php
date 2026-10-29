<?php
$apiKey = 'gnnwdxZQc54b9d99b234c49aPqNcit3f';

// Check city 256 in Komerce
// Is 256 really Kota Malang in Komerce?
// In Rajaongkir.com:
// 256 = Kota Malang
// But in Komerce API: What is ID 256?
$url = 'https://rajaongkir.komerce.id/api/v1/destination/city/18'; // Jawa Timur
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['key: ' . $apiKey],
    CURLOPT_TIMEOUT => 10
]);
$resJatim = json_decode(curl_exec($curl), true);
curl_close($curl);

echo "Cities in Jawa Timur (Komerce):\n";
if (!empty($resJatim['data'])) {
    foreach ($resJatim['data'] as $c) {
        if (stripos($c['name'], 'malang') !== false || stripos($c['name'], 'surabaya') !== false) {
            echo "   ID: {$c['id']} - Name: {$c['name']}\n";
        }
    }
}

