<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'SuperAdmin';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

echo "Has Jalankan Simulasi Demo (121 Item): " . (strpos($html, 'Jalankan Simulasi Demo (121 Item)') !== false ? "YES" : "NO") . PHP_EOL;
echo "Has Reset Data Demo: " . (strpos($html, 'Reset Data Demo') !== false ? "YES" : "NO") . PHP_EOL;
echo "Has Mulai Sesi: " . (strpos($html, 'Mulai Sesi Stok Opname Baru') !== false ? "YES" : "NO") . PHP_EOL;

