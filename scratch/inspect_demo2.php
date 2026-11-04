<?php
$f2 = 'c:/xampp/htdocs/inventory_zencare/database/isi/db_inventory (demo) 2.sql';
$fp = fopen($f2, 'r');
$count = 0;
while (($line = fgets($fp)) !== false && $count < 30) {
    if (strpos($line, 'INSERT INTO') !== false) {
        echo substr($line, 0, 80) . "\n";
        $count++;
    }
}
fclose($fp);

