<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin_toko';
$_SESSION['role'] = 'admin';
$_SESSION['nama_lengkap'] = 'Admin Toko';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

echo "Length: " . strlen($html) . PHP_EOL;
echo "Tabs in Admin: " . (strpos($html, 'TAB NAVIGASI AUDIT KHUSUS SUPERADMIN') !== false ? "YES" : "NO (Correct)") . PHP_EOL;
echo "Admin Notice: " . (strpos($html, 'Daftar Rekonsiliasi Selisih Fisik (Admin Toko)') !== false ? "YES (Correct)" : "NO") . PHP_EOL;
echo "Verifikasi Hilang (0) Bug: " . (strpos($html, 'Verifikasi Hilang (0)') !== false ? "YES (BUG!)" : "NO (Correct)") . PHP_EOL;

