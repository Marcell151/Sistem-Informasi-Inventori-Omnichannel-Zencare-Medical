<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'SuperAdmin (Pemilik)';
$_SESSION['id_cabang'] = 1;

ob_start();
require_once __DIR__ . '/../index.php';
$output = ob_get_clean();

echo "Render Superadmin Dashboard: Length = " . strlen($output) . " bytes\n";
if (strpos($output, 'KATALOG PRODUK') !== false && strpos($output, 'STOK SATUAN BESAR') !== false && strpos($output, 'STOK SATUAN KECIL') !== false) {
    echo "Superadmin check: PASS (All 3 new cards rendered)\n";
} else {
    echo "Superadmin check: FAIL\n";
}
