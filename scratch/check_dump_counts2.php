<?php
$f = file_get_contents(__DIR__ . '/../database/isi/db_inventory (demo) 1.sql');
preg_match_all('/INSERT INTO `?produk_variasi`?/i', $f, $m1);
echo "produk_variasi inserts: " . count($m1[0]) . "\n";
preg_match_all('/INSERT INTO `?produk_induk`?/i', $f, $m2);
echo "produk_induk inserts: " . count($m2[0]) . "\n";

