<?php
$lines = file('pos/pos.php');
foreach ($lines as $i => $l) {
    if (stripos($l, 'openPaymentModal') !== false) {
        echo ($i + 1) . ": " . trim($l) . "\n";
    }
}

