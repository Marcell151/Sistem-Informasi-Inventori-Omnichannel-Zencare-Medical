<?php
$lines = file('pos/pos.php');
for ($i = 440; $i < 580; $i++) {
    if (isset($lines[$i])) {
        if (strpos($lines[$i], 'product-card') !== false || strpos($lines[$i], 'data-stok-') !== false || strpos($lines[$i], 'data-batch') !== false) {
            echo ($i + 1) . ': ' . trim($lines[$i]) . "\n";
        }
    }
}

