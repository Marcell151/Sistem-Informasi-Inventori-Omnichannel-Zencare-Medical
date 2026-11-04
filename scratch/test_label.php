<?php
$baseUrl = 'http://localhost/inventory_zencare/';
$cookieSuper = __DIR__ . '/test_cookie_super.txt';
$ch = curl_init($baseUrl . 'login.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['username' => 'superadmin', 'password' => '']));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieSuper);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieSuper);
curl_exec($ch);
curl_close($ch);

$urls = [
    'admin/cetak_label_pengiriman.php?no_invoice=INV-20261018-041',
    'admin/cetak_label_pengiriman.php?no_invoice=INV-20261018-041&format=a4',
];

foreach ($urls as $u) {
    $ch = curl_init($baseUrl . $u);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieSuper);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $hasError = false;
    if (preg_match_all('/(Fatal error|Parse error|Warning|Notice|Deprecated):(.+)/i', $res, $m)) {
        $hasError = true;
    }
    echo "$u: HTTP $code | Length: " . strlen($res) . ($hasError ? " [ERROR DETECTED]" : " [OK]") . "\n";
}
if (file_exists($cookieSuper)) unlink($cookieSuper);
