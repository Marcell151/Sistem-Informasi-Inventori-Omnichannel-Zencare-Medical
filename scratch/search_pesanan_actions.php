<?php
$lines = file('admin/pesanan.php');
foreach ($lines as $i => $l) {
    if (stripos($l, 'name="aksi"') !== false || stripos($l, 'name="status_pesanan"') !== false) {
        echo ($i + 1) . ": " . trim($l) . "\n";
    }
}
