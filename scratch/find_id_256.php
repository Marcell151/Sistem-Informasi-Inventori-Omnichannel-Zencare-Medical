<?php
$apiKey = 'gnnwdxZQc54b9d99b234c49aPqNcit3f';

// Check all provinces to find where ID 256 is in Komerce!
$provData = json_decode(file_get_contents('cache/provinces_list.json'), true);
foreach ($provData['data'] as $p) {
    $pId = $p['id'];
    $url = "https://rajaongkir.komerce.id/api/v1/destination/city/$pId";
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['key: ' . $apiKey],
        CURLOPT_TIMEOUT => 10
    ]);
    $res = json_decode(curl_exec($curl), true);
    curl_close($curl);
    
    if (!empty($res['data'])) {
        foreach ($res['data'] as $c) {
            if ($c['id'] == 256 || $c['id'] == 221 || $c['id'] == 361) {
                echo "Province {$p['name']} (ID $pId): City ID {$c['id']} = {$c['name']}\n";
            }
        }
    }
}

