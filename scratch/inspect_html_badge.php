<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'owner';
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'SuperAdmin (Pemilik)';
$_GET['status_eval'] = 'semua';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

$pos = strpos($html, 'Alat Cek Asam Urat EasyTouch');
if ($pos !== false) {
    echo substr($html, $pos - 400, 600);
} else {
    echo "Product not found in HTML";
}

