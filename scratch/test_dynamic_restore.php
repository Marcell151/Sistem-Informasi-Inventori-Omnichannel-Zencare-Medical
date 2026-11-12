<?php
require_once __DIR__ . '/../config/koneksi.php';

$backupDir = 'C:\\xampp\\backup_data';
$cleanDump = $backupDir . '\\Demo_Bersih.sql';
if (!file_exists($cleanDump)) {
    $cleanDump = __DIR__ . '/../Demo_Bersih.sql';
}

$mysqlPath = 'C:\\xampp\\mysql\\bin\\mysql.exe';
$dbName = DB_NAME;
$dbUser = DB_USER;
$passArg = (!empty(DB_PASS)) ? "-p\"" . DB_PASS . "\"" : "";

// Using -e "source ..." with forward slashes is 100% reliable across all Windows shells without stdin redirection issues
$cleanDumpEscaped = str_replace('\\', '/', $cleanDump);
$cmd = "\"$mysqlPath\" -u $dbUser $passArg $dbName -e \"source $cleanDumpEscaped\" 2>&1";

echo "Running: $cmd\n";
$out = [];
$ret = 0;
exec($cmd, $out, $ret);

echo "Ret: $ret\n";
echo "Output: " . implode("\n", $out) . "\n";

// Now check if session 52 is gone:
$stmt = $pdo->query("SELECT id, kode_sesi FROM sesi_opname ORDER BY id DESC LIMIT 5");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Remaining sessions:\n";
foreach ($rows as $r) {
    echo "ID: {$r['id']}, Kode: {$r['kode_sesi']}\n";
}

