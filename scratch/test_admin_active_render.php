<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 2;
$_SESSION['role'] = 'admin';
$_SESSION['nama_lengkap'] = 'Admin Toko';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

echo "Has app-sidebar: " . (strpos($html, 'app-sidebar') !== false ? 'YES' : 'NO') . PHP_EOL;
echo "Has Pemisahan Tugas Pencacahan Fisik: " . (strpos($html, 'Pemisahan Tugas Pencacahan Fisik') !== false ? 'YES' : 'NO') . PHP_EOL;
echo "Has Tutup Penghitungan: " . (strpos($html, 'Tutup Penghitungan &amp; Buka Verifikasi') !== false ? 'YES' : 'NO') . PHP_EOL;
