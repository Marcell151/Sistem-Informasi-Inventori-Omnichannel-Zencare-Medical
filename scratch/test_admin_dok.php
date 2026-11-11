<?php
session_start();
$_SESSION['user_id'] = 2;
$_SESSION['role'] = 'admin';
$_SESSION['nama_lengkap'] = 'Admin Gudang';

ob_start();
include __DIR__ . '/../inventori/dokumentasi_testing.php';
$html = ob_get_clean();

echo "Render Admin dokumentasi_testing.php length: " . strlen($html) . "\n";
$hasSim = strpos($html, 'Jalankan Simulasi Demo (121 Item)') !== false;
$hasReset = strpos($html, '[Utilitas Demo] Reset Database') !== false;
echo "Admin can see Simulasi Demo button? " . ($hasSim ? "YES" : "NO") . "\n";
echo "Admin can see Reset Database button? " . ($hasReset ? "YES" : "NO") . "\n";

