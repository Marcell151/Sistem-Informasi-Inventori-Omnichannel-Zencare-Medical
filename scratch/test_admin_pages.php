<?php
$cookieFile = __DIR__ . '/test_cookie.txt';
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

$baseUrl = 'http://localhost/inventory_zencare/';

// Step 1: Login as admin_toko
$ch = curl_init($baseUrl . 'login.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'username' => 'admin_toko',
    'password' => '' // quick login allowed
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$loginResp = curl_exec($ch);
$loginCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$loginRedirect = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);

echo "1. Login Staf Result: HTTP $loginCode -> Redirect: $loginRedirect\n\n";

// Step 2: Test internal pages
$pages = [
    'index.php',
    'pos/pos.php',
    'admin/pesanan.php',
    'inventori/index.php',
    'inventori/stok_opname.php',
    'inventori/tambah_stok.php',
    'inventori/logistik_medis.php',
    'inventori/mutasi.php',
    'laporan/kartu_stok.php',
    'laporan/kartu_stok.php?id_produk=10', // paracetamol
    'laporan/kartu_stok.php?id_produk=1',  // tensimeter (alkes)
    'laporan/logistik_medis.php',
    'laporan/penjualan.php',
    'laporan/laba_rugi.php',
    'admin/cetak_label_pengiriman.php?id=1',
    'admin/cetak_label_pengiriman.php?id=1&format=a4',
    'inventori/cetak_nota_penerimaan.php?id=1',
];

$allPassed = true;
foreach ($pages as $p) {
    $url = $baseUrl . $p;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $hasError = false;
    $snippets = [];
    if (preg_match_all('/(Fatal error|Parse error|Warning|Notice|Deprecated):(.+)/i', $body, $m)) {
        $hasError = true;
        $snippets = array_slice($m[0], 0, 3);
        $allPassed = false;
    }

    echo "Endpoint: $p\n";
    echo "  Status Code: $code | Length: " . strlen($body) . " bytes\n";
    if ($hasError) {
        echo "  [FAIL] ERROR DETECTED:\n    " . implode("\n    ", $snippets) . "\n";
    } else {
        echo "  [OK] Clean\n";
    }
}

if ($allPassed) {
    echo "\n>>> ALL 17 PAGES PASSED WITH ZERO RUNTIME / PHP ERRORS <<<\n";
} else {
    echo "\n>>> SOME PAGES FAILED - INSPECT LOGS ABOVE <<<\n";
}

if (file_exists($cookieFile)) {
    unlink($cookieFile);
}
