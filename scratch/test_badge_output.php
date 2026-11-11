<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();
$pdo->query("UPDATE sesi_opname SET status = 'Aktif' WHERE id = 28");

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'owner';
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'SuperAdmin (Pemilik)';
$_GET['status_eval'] = 'semua';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

// Check if Alat Cek Asam Urat has Alkes (BMHP) badge
$pos = strpos($html, 'Alat Cek Asam Urat EasyTouch');
if ($pos !== false) {
    echo "Alat Cek Asam Urat EasyTouch FOUND in HTML!\n";
    echo substr($html, $pos - 350, 450) . PHP_EOL;
} else {
    echo "Not found in HTML\n";
}

$pos2 = strpos($html, 'Tensimeter Digital Omron');
if ($pos2 !== false) {
    echo "\nTensimeter Digital Omron FOUND in HTML!\n";
    echo substr($html, $pos2 - 350, 450) . PHP_EOL;
}

$pos3 = strpos($html, 'Amoxicillin 500mg');
if ($pos3 !== false) {
    echo "\nAmoxicillin 500mg FOUND in HTML!\n";
    echo substr($html, $pos3 - 350, 450) . PHP_EOL;
}
