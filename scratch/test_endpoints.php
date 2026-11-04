<?php
$pages = [
    'login.php',
    'ecommerce/index.php',
    'ecommerce/tentang_kami.php',
    'ecommerce/cara_pembelian.php',
    'admin/cetak_label_pengiriman.php?id=1',
    'inventori/cetak_nota_penerimaan.php?id=1',
];

$baseUrl = 'http://localhost/inventory_zencare/';

echo "=== TESTING PUBLIC & CRITICAL PAGES VIA HTTP ===\n";
foreach ($pages as $page) {
    $url = $baseUrl . $page;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    $hasPhpError = false;
    $errorSnippets = [];
    if (preg_match_all('/(Fatal error|Parse error|Warning|Notice|Deprecated):(.+)/i', $response, $matches)) {
        $hasPhpError = true;
        $errorSnippets = array_slice($matches[0], 0, 3);
    }

    echo "Page: $page\n";
    echo "  HTTP Code: $httpCode | Length: " . strlen($response) . " bytes\n";
    if ($curlErr) {
        echo "  CURL Error: $curlErr\n";
    }
    if ($hasPhpError) {
        echo "  PHP ERROR DETECTED:\n  - " . implode("\n  - ", $errorSnippets) . "\n";
    } else {
        echo "  Status: OK (No PHP errors in response)\n";
    }
    echo "\n";
}

