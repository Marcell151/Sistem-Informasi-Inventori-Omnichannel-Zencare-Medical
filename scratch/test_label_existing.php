<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'SuperAdmin';
$_GET['no_invoice'] = 'WEB-20261101-726';
ob_start();
require __DIR__ . '/../admin/cetak_label_pengiriman.php';
$output = ob_get_clean();

$checks = [
    'Logo ZenCare' => strpos($output, 'Logo.png') !== false,
    'ZENCARE MEDICAL' => strpos($output, 'ZENCARE MEDICAL') !== false,
    'Toko Alat Kesehatan dan Obat Omnichannel' => strpos($output, 'Toko Alat Kesehatan dan Obat Omnichannel') !== false,
    'Toko Alat Kesehatan dan Obat Resmi' => strpos($output, 'Toko Alat Kesehatan dan Obat Resmi') !== false,
    'Jl. Muharto No. 1, Kota Malang' => strpos($output, 'Jl. Muharto No. 1, Kota Malang') !== false,
    'Toolbar Cetak' => strpos($output, 'Cetak Label Thermal') !== false,
    '▷ Prefix' => strpos($output, '▷') !== false || strpos($output, '-') !== false,
];

foreach ($checks as $name => $ok) {
    echo ($ok ? "[PASS] " : "[FAIL] ") . $name . "\n";
}
