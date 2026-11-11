<?php
session_start();
$_SESSION['user_id'] = 2; // Admin
$_SESSION['role'] = 'admin';
$_SESSION['username'] = 'admin_toko';
$_SESSION['nama_lengkap'] = 'Admin Toko';

ob_start();
// Mock include of stok_opname
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

// Check if disabled button is rendered
if (strpos($html, 'Verifikasi Selisih (Menunggu Kasir: 121 Item)') !== false) {
    echo "SUCCESS: Admin view has disabled 'Verifikasi Selisih (Menunggu Kasir: 121 Item)' button in monitoring table.\n";
} else {
    echo "FAIL: Monitoring button not found.\n";
}

if (strpos($html, 'Kasir: 121 Item') !== false) {
    echo "SUCCESS: Toolbar has disabled button with badge 'Kasir: 121 Item'.\n";
} else {
    echo "FAIL: Toolbar button badge not found.\n";
}

if (strpos($html, 'Tutup Penghitungan') !== false) {
    echo "FAIL: 'Tutup Penghitungan' still exists in rendered HTML!\n";
} else {
    echo "SUCCESS: No 'Tutup Penghitungan' in rendered HTML.\n";
}

