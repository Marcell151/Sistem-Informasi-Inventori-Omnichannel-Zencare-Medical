<?php
ob_start();
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'Superadmin ZenCare';
$_GET['id_sesi'] = 7;
include 'inventori/cetak_berita_acara_opname.php';
$html = ob_get_clean();

echo "Length: " . strlen($html) . "\n";
if (strpos($html, 'OPN-20261015-001') !== false) {
    echo "[PASS] Berita Acara for OPN-20261015-001 rendered!\n";
}
if (strpos($html, 'SELESAI') !== false) {
    echo "[PASS] Status Selesai found!\n";
}

