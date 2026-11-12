<?php
$backupDir = 'C:\\xampp\\backup_data';
$cleanDump = $backupDir . '\\Demo_Bersih.sql';
$mysqlPath = 'C:\\xampp\\mysql\\bin\\mysql.exe';

$cmd = "\"$mysqlPath\" -u root inventory_zencare < \"$cleanDump\" 2>&1";
$out = [];
$ret = 0;
exec($cmd, $out, $ret);

echo "Command: $cmd\n";
echo "Return Code: $ret\n";
echo "Output: " . print_r($out, true) . "\n";
