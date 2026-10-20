<?php
$sql = file_get_contents(__DIR__ . '/../database/isi/db_inventory (19-10-26).sql');
preg_match_all('/CREATE TABLE `(.*?)`/i', $sql, $m);
echo "Tables in SQL dump:\n";
foreach ($m[1] as $tbl) {
    echo "- $tbl\n";
}
