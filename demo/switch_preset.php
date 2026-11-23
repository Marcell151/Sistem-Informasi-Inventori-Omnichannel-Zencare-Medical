<?php
// File: demo/switch_preset.php
// API ultra-cepat untuk mengganti preset database demo via shortcut keyboard (khusus localhost)
// Menggunakan in-place DELETE + INSERT dalam 1 transaksi InnoDB (tanpa DROP/CREATE table, tanpa spawn mysql.exe, tanpa session lock)
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remoteAddr, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Akses hanya diizinkan dari localhost.']);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method tidak diizinkan.']);
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$presetNum = (int) ($_POST['preset'] ?? 0);
if ($presetNum < 1 || $presetNum > 6) {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (is_array($json) && isset($json['preset'])) {
        $presetNum = (int) $json['preset'];
    }
}

$presetDir = __DIR__ . '/presets';
$backupDir = 'C:\\xampp\\backup_data';
$defaultSqlPath = file_exists(__DIR__ . '/../Demo_Bersih.sql')
    ? realpath(__DIR__ . '/../Demo_Bersih.sql')
    : ($backupDir . '\\Demo_Bersih.sql');

$presetMap = [
    1 => ['key' => 'default_demo4',          'path' => $defaultSqlPath],
    2 => ['key' => 'stok_normal',            'path' => $presetDir . '/demo_stok_normal.sql'],
    3 => ['key' => 'omnichannel',            'path' => $presetDir . '/demo_omnichannel.sql'],
    4 => ['key' => 'fefo_konversi',          'path' => $presetDir . '/demo_fefo_konversi.sql'],
    5 => ['key' => 'sudah_penerimaan',       'path' => $presetDir . '/demo_sudah_penerimaan.sql'],
    6 => ['key' => 'penerimaan_produk_baru', 'path' => $presetDir . '/demo_penerimaan_produk_baru.sql'],
];

if (!isset($presetMap[$presetNum])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Preset tidak valid (1-6).']);
    exit;
}

$sqlFile = $presetMap[$presetNum]['path'];
if (!$sqlFile || !file_exists($sqlFile)) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'File SQL preset tidak ditemukan.']);
    exit;
}

$t0 = microtime(true);
$sqlContent = file_get_contents($sqlFile);
if ($sqlContent === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Gagal membaca file SQL preset.']);
    exit;
}

// Kumpulkan daftar tabel & statement INSERT dari dump SQL
preg_match_all('/^CREATE TABLE `([^`]+)`/m', $sqlContent, $tblMatches);
$dumpTables = $tblMatches[1] ?? [];

$insertLines = [];
$lines = preg_split('/\r?\n/', $sqlContent);
foreach ($lines as $line) {
    if (strncmp($line, 'INSERT INTO ', 12) === 0) {
        $insertLines[] = $line;
    }
}

// Cek apakah seluruh tabel di dump sudah ada di database aktif (fast-path in-place DML)
$existingTables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$existingMap = array_flip($existingTables);
$canFastSwap = !empty($dumpTables);
foreach ($dumpTables as $tbl) {
    if (!isset($existingMap[$tbl])) {
        $canFastSwap = false;
        break;
    }
}

if ($canFastSwap) {
    try {
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        $pdo->exec('SET UNIQUE_CHECKS=0');
        $pdo->exec("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");
        $pdo->beginTransaction();

        foreach ($dumpTables as $tbl) {
            $pdo->exec("DELETE FROM `{$tbl}`");
        }
        foreach ($insertLines as $insSql) {
            $pdo->exec($insSql);
        }

        $pdo->commit();
        $pdo->exec('SET UNIQUE_CHECKS=1');
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

        echo json_encode([
            'ok'     => true,
            'mode'   => 'fast_pdo',
            'preset' => $presetNum,
            'key'    => $presetMap[$presetNum]['key'],
            'ms'     => round((microtime(true) - $t0) * 1000),
        ]);
        exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        try {
            $pdo->exec('SET UNIQUE_CHECKS=1');
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        } catch (Throwable $ignored) {}
        // Lanjut ke fallback mysql.exe di bawah jika ada ketidaksesuaian skema
    }
}

// Fallback penuh via mysql.exe (misal jika tabel belum terbentuk)
$mysqlPath = 'C:\\xampp\\mysql\\bin\\mysql.exe';
if (!file_exists($mysqlPath)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Gagal fast-swap dan mysql.exe tidak ditemukan.']);
    exit;
}

$dbName  = defined('DB_NAME') ? DB_NAME : 'db_inventory';
$dbUser  = defined('DB_USER') ? DB_USER : 'root';
$passArg = (defined('DB_PASS') && DB_PASS !== '') ? '-p"' . DB_PASS . '"' : '';
$escaped = str_replace('\\', '/', realpath($sqlFile));

$out = [];
$ret = 0;
exec("\"$mysqlPath\" -u $dbUser $passArg $dbName -e \"source $escaped\" 2>&1", $out, $ret);

if ($ret !== 0) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => implode("\n", $out)]);
    exit;
}

echo json_encode([
    'ok'     => true,
    'mode'   => 'mysql_cli',
    'preset' => $presetNum,
    'key'    => $presetMap[$presetNum]['key'],
    'ms'     => round((microtime(true) - $t0) * 1000),
]);
