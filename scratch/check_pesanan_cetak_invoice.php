<?php
$lines = file('admin/pesanan.php');
foreach ($lines as $i => $l) {
    if (strpos($l, 'cetak_invoice') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($l) . "\n";
    }
}

