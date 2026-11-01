<?php
$lines = file('inventori/stok_opname.php');
foreach ($lines as $i => $l) {
    if (stripos($l, 'viewSerialNumbers') !== false || stripos($l, 'modal_view_sn') !== false) {
        echo ($i + 1) . ": " . trim($l) . "\n";
    }
}

