<?php
// Test E-Commerce invoice rendering
$_GET['no_invoice'] = 'WEB-20261101-726';
$_GET['format'] = 'a4';
ob_start();
require __DIR__ . '/../pos/cetak_invoice.php';
$output = ob_get_clean();

echo "=== TEST E-COMMERCE (WEB-20261101-726) ===\n";
if (preg_match('/Informasi Penerima \/ Pelanggan.*?<strong>(.*?)<\/strong>/s', $output, $m)) {
    echo "Penerima/Pelanggan: " . trim($m[1]) . "\n";
}
if (preg_match('/Admin \/ Petugas Logistik: <strong>(.*?)<\/strong>/s', $output, $m)) {
    echo "Petugas di Card: " . trim($m[1]) . "\n";
}
if (preg_match('/Petugas Logistik & Admin Toko.*?text-decoration: underline.*?>(.*?)<\/div>/s', $output, $m)) {
    echo "Penanggung Jawab di TTD: " . trim($m[1]) . "\n";
}

// Test POS invoice rendering
$_GET['no_invoice'] = 'POS-20261101-2875';
$_GET['format'] = 'thermal';
ob_start();
require __DIR__ . '/../pos/cetak_invoice.php';
$outputPos = ob_get_clean();

echo "\n=== TEST POS (POS-20261101-2875) ===\n";
if (preg_match('/<span>Kasir<\/span>\s*<span>(.*?)<\/span>/s', $outputPos, $m)) {
    echo "Kasir di Thermal: " . trim($m[1]) . "\n";
}
if (preg_match('/<span>Pelanggan<\/span>\s*<span>(.*?)<\/span>/s', $outputPos, $m)) {
    echo "Pelanggan di Thermal: " . trim($m[1]) . "\n";
}

