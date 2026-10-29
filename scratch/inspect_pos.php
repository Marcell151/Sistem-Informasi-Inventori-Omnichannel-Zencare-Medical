<?php
$lines = file('pos/pos.php');
foreach ($lines as $n => $l) {
    if (stripos($l, 'zencare_pos_cart') !== false) {
        echo ($n + 1) . ': ' . trim($l) . "\n";
    }
}
