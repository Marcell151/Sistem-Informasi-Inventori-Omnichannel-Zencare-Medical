<?php
require_once __DIR__ . '/../config/koneksi.php';

function testRole($role, $nama, $userId) {
    $_SESSION['id_user'] = $userId;
    $_SESSION['username'] = strtolower($role);
    $_SESSION['role'] = $role;
    $_SESSION['nama_lengkap'] = $nama;

    ob_start();
    include __DIR__ . '/../inventori/stok_opname.php';
    $html = ob_get_clean();

    echo "=== ROLE: $role ($nama) ===\n";
    echo "Has 'Mulai Sesi Stok Opname Baru': " . (strpos($html, 'Mulai Sesi Stok Opname Baru') !== false ? 'YES' : 'NO') . "\n";
    echo "Has 'Reset Data Demo': " . (strpos($html, 'Reset Data Demo') !== false ? 'YES' : 'NO') . "\n";
    echo "Has 'Dokumentasi Pengujian Black-Box': " . (strpos($html, 'Dokumentasi Pengujian Black-Box') !== false ? 'YES' : 'NO') . "\n";
    echo "Has 'Hanya Superadmin': " . (strpos($html, 'Hanya Superadmin') !== false ? 'YES' : 'NO') . "\n";
    echo "Has '1. Central Server Freeze': " . (strpos($html, '1. Central Server Freeze') !== false ? 'YES' : 'NO') . "\n";
    echo "\n";
}

testRole('superadmin', 'SuperAdmin Pemilik', 1);
testRole('admin', 'Admin Toko', 2);
testRole('kasir', 'Kasir Toko', 4);

