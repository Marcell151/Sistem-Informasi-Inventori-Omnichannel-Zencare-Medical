<?php
$lines = file('admin/pesanan.php');
foreach ($lines as $i => $l) {
    if (stripos($l, 'sesiopnameaktif') !== false || stripos($l, 'opname') !== false) {
        echo ($i + 1) . ": " . trim($l) . "\n";
    }
}

