<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'owner';
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'SuperAdmin (Pemilik)';
$_GET['status_eval'] = 'sesuai'; // Test viewing Sesuai tab as Superadmin

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

echo "Length: " . strlen($html) . PHP_EOL;
echo "Tabs in Superadmin: " . (strpos($html, 'TAB NAVIGASI AUDIT KHUSUS SUPERADMIN') !== false ? "YES (Correct)" : "NO") . PHP_EOL;
echo "Klop (Sesuai) Badge: " . (strpos($html, 'Klop (Sesuai)') !== false ? "YES (Correct)" : "NO") . PHP_EOL;
echo "Superadmin Koreksi Button: " . (strpos($html, 'openModalKoreksiSuperadmin') !== false ? "YES (Correct)" : "NO") . PHP_EOL;
echo "Verifikasi Hilang (0) Bug: " . (strpos($html, 'Verifikasi Hilang (0)') !== false ? "YES (BUG!)" : "NO (Correct)") . PHP_EOL;

