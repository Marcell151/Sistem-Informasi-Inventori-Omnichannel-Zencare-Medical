<?php
// scratch/render_page.php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

$role = $argv[1] ?? 'admin';
$targetRel = $argv[2] ?? 'index.php';
$outName = $argv[3] ?? 'temp.html';
$queryString = $argv[4] ?? '';

// Setup global $_SERVER
$_SERVER['DOCUMENT_ROOT'] = 'C:/xampp/htdocs';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SCRIPT_NAME'] = '/inventory_zencare/' . $targetRel;
$_SERVER['PHP_SELF'] = '/inventory_zencare/' . $targetRel;
$_SERVER['REQUEST_URI'] = '/inventory_zencare/' . $targetRel . ($queryString ? '?' . $queryString : '');

// Setup $_GET
$_GET = [];
if ($queryString) {
    parse_str($queryString, $_GET);
}

// Setup session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($role === 'superadmin') {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'superadmin';
    $_SESSION['nama_lengkap'] = 'SuperAdmin (Pemilik)';
    $_SESSION['role'] = 'superadmin';
} elseif ($role === 'admin') {
    $_SESSION['user_id'] = 2;
    $_SESSION['username'] = 'admin_toko';
    $_SESSION['nama_lengkap'] = 'Admin Toko';
    $_SESSION['role'] = 'admin';
} elseif ($role === 'kasir') {
    $_SESSION['user_id'] = 4;
    $_SESSION['username'] = 'kasir_toko';
    $_SESSION['nama_lengkap'] = 'Kasir (Front Office)';
    $_SESSION['role'] = 'kasir';
} elseif ($role === 'pelanggan') {
    $_SESSION['user_id'] = 3;
    $_SESSION['username'] = 'pelanggan1';
    $_SESSION['nama_lengkap'] = 'Budi Santoso';
    $_SESSION['role'] = 'pelanggan';
    $_SESSION['keranjang'] = [
        1 => ['id_variasi' => 1, 'nama' => 'Amoxicillin 500mg (Box)', 'qty' => 1, 'harga' => 70000, 'tipe' => 'obat']
    ];
} else {
    // guest
    $_SESSION = [];
}
session_write_close();

$baseDir = dirname(__DIR__);
$targetPath = $baseDir . '/' . ltrim($targetRel, '/');

if (!file_exists($targetPath)) {
    die("File not found: " . $targetPath . "\n");
}

ob_start();
chdir(dirname($targetPath));
include $targetPath;
$content = ob_get_clean();

// Strip any unwanted notice or warning before <!DOCTYPE
if (($docPos = stripos($content, '<!DOCTYPE')) !== false && $docPos > 0) {
    $content = substr($content, $docPos);
}

// Ensure cache dir exists
$cacheDir = $baseDir . '/scratch/cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

// In case base tag is missing, add base tag for assets
if (stripos($content, '<base') === false && stripos($content, '<head>') !== false) {
    $content = preg_replace('/<head>/i', '<head><base href="http://localhost/inventory_zencare/">', $content, 1);
}

file_put_contents($cacheDir . '/' . $outName, $content);
echo "Rendered " . $outName . " (" . strlen($content) . " bytes)\n";
