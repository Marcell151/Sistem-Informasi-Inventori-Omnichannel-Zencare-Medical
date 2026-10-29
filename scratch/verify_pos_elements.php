<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'Kasir Test';
$_SESSION['id_cabang'] = 1;
ob_start();
include 'pos/pos.php';
$out = ob_get_clean();

$checks = [
    'POS ZenCare Medical',
    'Dashboard Utama',
    'Toko Online',
    'Keluar',
    'Batch Tertib',
    'panel_batch_terlewat'
];

foreach ($checks as $item) {
    if (strpos($out, $item) !== false) {
        echo "FOUND: $item\n";
    } else {
        echo "MISSING: $item\n";
    }
}

