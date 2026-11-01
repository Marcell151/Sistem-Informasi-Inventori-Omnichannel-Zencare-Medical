<?php
$f = file_get_contents(__DIR__ . '/../database/isi/db_inventory (demo) 1.sql');
if (preg_match('/INSERT INTO `?produk_induk`? VALUES\s*\((.*?)\);/is', $f, $m)) {
    // count rows
    preg_match_all('/\(([0-9]+),/i', '(' . $m[1], $matches);
    echo "Dump produk_induk row count: " . count($matches[0]) . "\n";
    echo "Dump max induk ID: " . max($matches[1]) . "\n";
}
if (preg_match('/INSERT INTO `?produk_variasi`? VALUES\s*\((.*?)\);/is', $f, $m)) {
    preg_match_all('/\(([0-9]+),/i', '(' . $m[1], $matches);
    echo "Dump produk_variasi row count: " . count($matches[0]) . "\n";
    echo "Dump max variasi ID: " . max($matches[1]) . "\n";
}

