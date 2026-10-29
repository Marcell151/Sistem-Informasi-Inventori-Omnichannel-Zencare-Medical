<?php
$lines = file('api/checkout.php');
foreach ($lines as $i => $l) {
    if (stripos($l, 'enabled_payments') !== false || stripos($l, 'kategori_pembayaran') !== false || stripos($l, 'snap_params') !== false || stripos($l, 'params') !== false) {
        echo ($i + 1) . ': ' . trim($l) . "\n";
    }
}

