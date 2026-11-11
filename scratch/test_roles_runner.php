<?php
$role = $argv[1] ?? 'admin';
$userId = ($role === 'superadmin') ? 1 : (($role === 'admin') ? 2 : 4);

session_start();
$_SESSION['user_id'] = $userId;
$_SESSION['username'] = $role;
$_SESSION['role'] = $role;
$_SESSION['nama_lengkap'] = ucfirst($role) . ' User';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

echo "=== ROLE: $role ===\n";
echo "Has 'Mulai Sesi Stok Opname Baru (Bekukan Transaksi)': " . (strpos($html, 'Mulai Sesi Stok Opname Baru (Bekukan Transaksi)') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'Reset Data Demo': " . (strpos($html, 'Reset Data Demo') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'Dokumentasi Pengujian Black-Box': " . (strpos($html, 'Dokumentasi Pengujian Black-Box') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'Hanya Superadmin': " . (strpos($html, 'Hanya Superadmin') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'Central Server Freeze' card: " . (strpos($html, 'Central Server Freeze') !== false ? 'YES' : 'NO') . "\n";

