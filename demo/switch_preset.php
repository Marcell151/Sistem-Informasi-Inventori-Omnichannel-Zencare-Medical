<?php
// File: demo/switch_preset.php
// API ringan untuk mengganti preset database demo via shortcut keyboard (khusus localhost)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

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
require_once __DIR__ . '/../config/opname_helper.php';

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
$mysqlPath = 'C:\\xampp\\mysql\\bin\\mysql.exe';

if (!file_exists($mysqlPath)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'mysql.exe tidak ditemukan.']);
    exit;
}
if (!$sqlFile || !file_exists($sqlFile)) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'File SQL preset tidak ditemukan.']);
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

// Refresh cache status sesi opname
if (isset($pdo) && function_exists('cekSesiOpnameAktif')) {
    cekSesiOpnameAktif($pdo, true);
}

echo json_encode([
    'ok'     => true,
    'preset' => $presetNum,
    'key'    => $presetMap[$presetNum]['key'],
]);
