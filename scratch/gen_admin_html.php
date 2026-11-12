<?php
session_start();
$_SESSION['user_id'] = 2;
$_SESSION['role'] = 'admin';
$_SESSION['nama_lengkap'] = 'Admin Toko';
$_GET['status_eval'] = 'berselisih';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

// Add base tag for assets
$html = str_replace('<head>', '<head><base href="http://localhost/inventory_zencare/inventori/">', $html);
file_put_contents(__DIR__ . '/admin_opname_rendered.html', $html);
echo "Rendered static HTML successfully!\n";
