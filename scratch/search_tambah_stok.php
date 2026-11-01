<?php
$lines = file('inventori/tambah_stok.php');
foreach ($lines as $i => $l) {
    if (stripos($l, 'pengganti') !== false || stripos($l, 'retur') !== false) {
        if (stripos($l, 'button') !== false || stripos($l, 'onclick') !== false || stripos($l, 'modal') !== false || stripos($l, '<form') !== false) {
            echo ($i + 1) . ": " . trim($l) . "\n";
        }
    }
}

