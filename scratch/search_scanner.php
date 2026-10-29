<?php
$lines = file('pos/pos.php');
foreach ($lines as $i => $l) {
    if (strpos($l, 'onScanSuccess') !== false || strpos($l, 'barcode_input') !== false || strpos($l, 'handleBarcodeInput') !== false || strpos($l, 'scanBarcode') !== false) {
        echo ($i + 1) . ': ' . trim($l) . "\n";
    }
}

