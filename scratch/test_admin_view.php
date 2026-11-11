<?php
session_start();
$_SESSION['user_id'] = 2;
$_SESSION['role'] = 'admin';
$_SESSION['nama_lengkap'] = 'Admin Gudang';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

echo "Render Admin stok_opname.php length: " . strlen($html) . "\n";
$hasReset = strpos($html, '[Utilitas Demo] Reset Data Demo') !== false;
$hasMulai = strpos($html, 'Mulai Sesi Stok Opname Baru') !== false;
echo "Admin can see Reset button? " . ($hasReset ? "YES" : "NO") . "\n";
echo "Admin can see Mulai Sesi button? " . ($hasMulai ? "YES" : "NO") . "\n";

