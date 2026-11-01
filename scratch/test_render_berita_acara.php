<?php
ob_start();
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'Superadmin ZenCare';
$_GET['id_sesi'] = 3;
include 'inventori/cetak_berita_acara_opname.php';
$out = ob_get_clean();

echo "Length: " . strlen($out) . "\n";
if (strpos($out, 'ZENCARE MEDICAL') !== false && strpos($out, 'Petugas Pemeriksa Lapangan') !== false && strpos($out, 'Penanggung Jawab Toko (Superadmin / Pemilik)') !== false) {
    echo "[PASS] Berita Acara rendered successfully with Logo header and TTD sections.\n";
} else {
    echo "[FAIL] Rendering mismatch.\n";
}

