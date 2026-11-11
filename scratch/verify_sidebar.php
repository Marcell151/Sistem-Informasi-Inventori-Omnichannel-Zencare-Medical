<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'SuperAdmin (Pemilik)';

ob_start();
require_once __DIR__ . '/../config/layout.php';
layoutSidebar('stok_opname');
$html = ob_get_clean();

echo "Sidebar contains 'Dokumentasi Testing': " . (strpos($html, 'Dokumentasi Testing') !== false ? "YES (Still there!)" : "NO (Clean, successfully removed!)") . PHP_EOL;

