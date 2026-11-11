<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'owner';
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'SuperAdmin (Pemilik)';
$_GET['status_eval'] = 'sesuai'; // In 'sesuai' tab

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

$pos = strpos($html, 'Alat Cek Asam Urat EasyTouch');
if ($pos !== false) {
    echo "Found at pos $pos:\n";
    echo substr($html, $pos - 350, 500) . PHP_EOL;
} else {
    echo "Product not found in 'sesuai' tab. Checking why...\n";
    $pos2 = strpos($html, 'EasyTouch');
    echo "EasyTouch pos: " . var_export($pos2, true) . PHP_EOL;
}

