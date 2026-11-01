<?php
$lines = file('pos/pos.php');
foreach ($lines as $i => $l) {
    if (stripos($l, 'btn_pay') !== false) {
        echo ($i + 1) . ": " . trim($l) . "\n";
    }
}

