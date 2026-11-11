<?php
$content = file_get_contents(__DIR__ . '/../inventori/dokumentasi_testing.php');

$pos1 = strpos($content, 'localhost/inventory_zencare/inventori/stok_opname.php');
while ($pos1 !== false) {
    echo "Match at $pos1:\n";
    echo substr($content, $pos1 - 250, 400) . PHP_EOL . "=====\n";
    $pos1 = strpos($content, 'localhost/inventory_zencare/inventori/stok_opname.php', $pos1 + 10);
}

