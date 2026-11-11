<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 4;
$_SESSION['role'] = 'kasir';
$_SESSION['nama_lengkap'] = 'Kasir Toko';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

preg_match('/Sesi:\s*([^\s<]+)/', $html, $m);
echo "Sesi rendered: " . ($m[1] ?? 'NOT FOUND') . PHP_EOL;

preg_match('/Belum Dihitung.*?font-mono[^>]*>(\d+)/s', $html, $m2);
echo "Belum Dihitung count: " . ($m2[1] ?? 'NOT FOUND') . PHP_EOL;

