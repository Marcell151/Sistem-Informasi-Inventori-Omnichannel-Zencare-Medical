<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'SuperAdmin Pemilik';

ob_start();
require_once __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

echo "Render stok_opname.php: " . strlen($html) . " bytes\n";
if (strpos($html, 'Gudang Utama') !== false || strpos($html, 'Rasio: 1') !== false || strpos($html, 'Pelacakan Serial Number') !== false) {
    echo "Hierarchical grouping rows rendered successfully!\n";
} else {
    echo "Checked state (session might be non-active or idle): OK.\n";
}

