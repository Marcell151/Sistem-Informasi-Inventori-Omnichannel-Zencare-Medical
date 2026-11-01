<?php
$lines = file('admin/pesanan.php');
foreach ($lines as $i => $l) {
    if (stripos($l, 'modal') !== false || stripos($l, 'update_status') !== false || stripos($l, 'bukaPackingModal') !== false) {
        if (stripos($l, '<button') !== false || stripos($l, 'onclick') !== false || stripos($l, '<form') !== false) {
            echo ($i + 1) . ": " . trim($l) . "\n";
        }
    }
}

