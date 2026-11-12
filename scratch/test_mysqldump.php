<?php
require_once __DIR__ . '/../config/config.php';

$backupDir = 'C:\\xampp\\backup_data';
$dbName = defined('DB_NAME') ? DB_NAME : 'db_inventory';
$dbUser = defined('DB_USER') ? DB_USER : 'root';
$passArg = (defined('DB_PASS') && DB_PASS !== '') ? "-p\"" . DB_PASS . "\"" : "";
$backupFile = $backupDir . '\\test_dump_' . date('Ymd_His') . '.sql';

$mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
$cmd = "\"$mysqldumpPath\" -u $dbUser $passArg $dbName > \"$backupFile\" 2>&1";
exec($cmd, $out, $ret);

echo "cmd: $cmd\n";
echo "ret: $ret\n";
echo "size: " . filesize($backupFile) . " bytes\n";
unlink($backupFile);

