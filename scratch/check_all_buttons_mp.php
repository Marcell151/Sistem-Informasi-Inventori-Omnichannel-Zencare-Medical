<?php
$lines = file(__DIR__ . '/../admin/master_produk.php');
foreach ($lines as $i => $l) {
    if (stripos($l, '<button') !== false || stripos($l, 'onclick=') !== false) {
        echo ($i + 1) . ': ' . trim($l) . PHP_EOL;
    }
}
