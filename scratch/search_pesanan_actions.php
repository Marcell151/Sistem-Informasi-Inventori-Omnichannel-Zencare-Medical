<?php
$lines = file('admin/pesanan.php');
foreach ($lines as $i => $l) {
    if (stripos($l, 'cetak') !== false || stripos($l, 'invoice') !== false || stripos($l, 'label') !== false) {
        echo ($i + 1) . ': ' . trim($l) . "\n";
    }
}

