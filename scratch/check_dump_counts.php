<?php
$f = file_get_contents(__DIR__ . '/../database/isi/db_inventory (demo) 1.sql');
if (preg_match('/INSERT INTO `produk_variasi` VALUES (.*?);/s', $f, $m)) {
    $rows = explode('),(', $m[1]);
    echo "Total rows in dump produk_variasi: " . count($rows) . "\n";
}
if (preg_match('/INSERT INTO `produk_induk` VALUES (.*?);/s', $f, $m)) {
    $rows = explode('),(', $m[1]);
    echo "Total rows in dump produk_induk: " . count($rows) . "\n";
}

