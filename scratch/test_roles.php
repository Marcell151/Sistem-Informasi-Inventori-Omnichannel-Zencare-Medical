<?php
$baseUrl = 'http://localhost/inventory_zencare/';

// Test Superadmin
$cookieSuper = __DIR__ . '/test_cookie_super.txt';
$ch = curl_init($baseUrl . 'login.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['username' => 'superadmin', 'password' => '']));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieSuper);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieSuper);
curl_exec($ch);
curl_close($ch);

$superPages = [
    'laporan/penjualan.php',
    'pos/pos.php',
    'laporan/kartu_stok.php?id_produk=10'
];

echo "=== SUPERADMIN CHECKS ===\n";
foreach ($superPages as $sp) {
    $ch = curl_init($baseUrl . $sp);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieSuper);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $hasError = false;
    if (preg_match_all('/(Fatal error|Parse error|Warning|Notice|Deprecated):(.+)/i', $res, $m)) {
        $hasError = true;
    }
    echo "$sp: HTTP $code | Length: " . strlen($res) . ($hasError ? " [ERROR DETECTED]" : " [OK]") . "\n";
}
if (file_exists($cookieSuper)) unlink($cookieSuper);

// Test Kasir
$cookieKasir = __DIR__ . '/test_cookie_kasir.txt';
$ch = curl_init($baseUrl . 'login.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['username' => 'kasir_toko', 'password' => '']));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieKasir);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieKasir);
curl_exec($ch);
curl_close($ch);

echo "\n=== KASIR CHECKS ===\n";
$ch = curl_init($baseUrl . 'pos/pos.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieKasir);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$hasError = false;
if (preg_match_all('/(Fatal error|Parse error|Warning|Notice|Deprecated):(.+)/i', $res, $m)) {
    $hasError = true;
}
echo "pos/pos.php: HTTP $code | Length: " . strlen($res) . ($hasError ? " [ERROR DETECTED]" : " [OK]") . "\n";
if (file_exists($cookieKasir)) unlink($cookieKasir);

