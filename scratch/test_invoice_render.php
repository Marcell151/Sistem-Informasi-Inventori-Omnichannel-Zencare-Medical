<?php
$_GET['no_invoice'] = 'POS-20261101-2875';
ob_start();
require __DIR__ . '/../pos/cetak_invoice.php';
$output = ob_get_clean();

echo "Rendered length: " . strlen($output) . " bytes\n";
if (strpos($output, 'ZENCARE MEDICAL') !== false && strpos($output, 'HUF-TMP-26.A') !== false) {
    echo "SUCCESS: Found brand and batch data in output!\n";
} else {
    echo "ERROR: Expected text not found in output!\n";
}

// Test A4 mode
$_GET['format'] = 'a4';
ob_start();
require __DIR__ . '/../pos/cetak_invoice.php';
$outputA4 = ob_get_clean();
echo "Rendered A4 length: " . strlen($outputA4) . " bytes\n";
if (strpos($outputA4, 'mode-a4') !== false) {
    echo "SUCCESS: A4 mode applied!\n";
}

// Test E-Commerce invoice
$_GET['no_invoice'] = 'WEB-20261101-726';
$_GET['format'] = 'a4';
ob_start();
require __DIR__ . '/../pos/cetak_invoice.php';
$outputWeb = ob_get_clean();
echo "Rendered WEB length: " . strlen($outputWeb) . " bytes\n";
if (strpos($outputWeb, 'Budi Santoso') !== false) {
    echo "SUCCESS: Customer name found in E-Commerce invoice!\n";
}

