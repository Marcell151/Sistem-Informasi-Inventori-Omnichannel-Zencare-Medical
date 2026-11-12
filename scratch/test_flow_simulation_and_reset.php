<?php
// Test full simulation and reset flow
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/opname_helper.php';

echo "1. Current sessions count: ";
$s = $pdo->query("SELECT count(*) FROM sesi_opname")->fetchColumn();
echo $s . "\n";

echo "2. Running simulation...\n";
$_POST['aksi'] = 'jalankan_simulasi_demo';
$stopAtVerifikasi = false;
$userId = 1;
include __DIR__ . '/run_full_opname_simulation.php';

$sAfterSim = $pdo->query("SELECT id, kode_sesi, status FROM sesi_opname ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
echo "New session created: ID {$sAfterSim['id']} - {$sAfterSim['kode_sesi']} ({$sAfterSim['status']})\n";

$anomaliCount = $pdo->query("SELECT count(*) FROM detail_opname_batch WHERE id_sesi = {$sAfterSim['id']} AND (selisih != 0 OR fisik_rusak > 0 OR fisik_expired > 0)")->fetchColumn();
echo "Anomalies in session: $anomaliCount\n";

echo "3. Now executing reset_database_demo logic...\n";
$backupDir = 'C:\\xampp\\backup_data';
$dbName = defined('DB_NAME') ? DB_NAME : 'db_inventory';
$dbUser = defined('DB_USER') ? DB_USER : 'root';
$passArg = (defined('DB_PASS') && DB_PASS !== '') ? "-p\"" . DB_PASS . "\"" : "";

$cleanDump = $backupDir . '\\Demo_Bersih.sql';
$cleanDumpEscaped = str_replace('\\', '/', $cleanDump);
$mysqlPath = 'C:\\xampp\\mysql\\bin\\mysql.exe';
$cmd = "\"$mysqlPath\" -u $dbUser $passArg $dbName -e \"source $cleanDumpEscaped\" 2>&1";
exec($cmd, $out, $ret);

echo "Reset execution ret code: $ret\n";

$sAfterReset = $pdo->query("SELECT count(*) FROM sesi_opname")->fetchColumn();
echo "Sessions count after reset: $sAfterReset (Should be 4)\n";

$lastSesi = $pdo->query("SELECT id, kode_sesi FROM sesi_opname ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
echo "Latest session after reset: ID {$lastSesi['id']} - {$lastSesi['kode_sesi']}\n";

$activeSesi = cekSesiOpnameAktif($pdo, true);
echo "Active session after reset: " . ($activeSesi ? $activeSesi['kode_sesi'] : 'None (Correct)') . "\n";

