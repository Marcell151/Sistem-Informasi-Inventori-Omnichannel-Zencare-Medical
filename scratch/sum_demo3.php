<?php
$f3 = 'c:/xampp/htdocs/inventory_zencare/database/isi/db_inventory (demo) 3.sql';
$c = file_get_contents($f3);

// Find line 633: stok_toko
preg_match('/INSERT INTO `stok_toko` VALUES\s*(.*?);/s', $c, $m);
if (!empty($m[1])) {
    preg_match_all('/\((\d+),(\d+),(\d+)\)/', $m[1], $items);
    echo "DEMO 3 stok_toko:\n";
    echo "- Rows: " . count($items[0]) . "\n";
    echo "- Sum stok: " . array_sum($items[3]) . "\n";
}

// Find line 606: stok_batch
preg_match('/INSERT INTO `stok_batch` VALUES\s*(.*?);/s', $c, $m2);
if (!empty($m2[1])) {
    preg_match_all('/\((\d+),(\d+),\'[^\']*\',\'[^\']*\',(\d+),/', $m2[1], $items2);
    echo "DEMO 3 stok_batch:\n";
    echo "- Rows: " . count($items2[0]) . "\n";
    echo "- Sum stok_sisa: " . array_sum($items2[3]) . "\n";
}
