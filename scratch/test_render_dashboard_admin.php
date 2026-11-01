<?php
session_start();
$_SESSION['user_id'] = 2;
$_SESSION['role'] = 'admin';
$_SESSION['nama_lengkap'] = 'Admin Operasional';
$_SESSION['id_cabang'] = 1;

ob_start();
require_once __DIR__ . '/../index.php';
$output = ob_get_clean();

echo "Render Admin Dashboard: Length = " . strlen($output) . " bytes\n";
if (strpos($output, 'KATALOG PRODUK') !== false && strpos($output, 'STOK SATUAN BESAR') !== false && strpos($output, 'STOK SATUAN KECIL') !== false) {
    echo "Admin check: PASS (All 3 new cards rendered)\n";
} else {
    echo "Admin check: FAIL\n";
}

