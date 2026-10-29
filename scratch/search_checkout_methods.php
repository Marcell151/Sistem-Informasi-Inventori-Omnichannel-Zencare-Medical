<?php
$lines = file('zencare_checkout.php');
foreach ($lines as $i => $l) {
    if (stripos($l, 'qris') !== false || stripos($l, 'metode_pembayaran') !== false || stripos($l, 'payment_method') !== false || stripos($l, 'virtual account') !== false) {
        echo ($i + 1) . ': ' . trim($l) . "\n";
    }
}

