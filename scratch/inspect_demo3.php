<?php
$f3 = 'c:/xampp/htdocs/inventory_zencare/database/isi/db_inventory (demo) 3.sql';
$fp = fopen($f3, 'r');
$lineNum = 0;
while (($line = fgets($fp)) !== false) {
    $lineNum++;
    if (strpos($line, 'INSERT INTO `stok_toko`') !== false || strpos($line, 'INSERT INTO `stok_batch`') !== false) {
        echo "Line $lineNum: " . substr($line, 0, 100) . "...\n";
    }
}
fclose($fp);

