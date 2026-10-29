<?php
$lines = file('pos/pos.php');
foreach ($lines as $i => $l) {
    if (strpos($l, 'satuan_tipe') !== false || strpos($l, 'uomType') !== false) {
        echo ($i + 1) . ': ' . trim($l) . "\n";
    }
}

