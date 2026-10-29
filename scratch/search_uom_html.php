<?php
$lines = file('pos/pos.php');
foreach ($lines as $i => $l) {
    if (strpos($l, 'id="uom_') !== false || strpos($l, 'uom_') !== false) {
        echo ($i + 1) . ': ' . trim($l) . "\n";
    }
}

