<?php
// Session setup and direct include for screenshots
session_start();
$role = $_GET['role'] ?? 'superadmin';
if ($role === 'superadmin') {
    $_SESSION['user_id']      = 1;
    $_SESSION['username']     = 'superadmin';
    $_SESSION['nama_lengkap'] = 'SuperAdmin (Pemilik)';
    $_SESSION['role']         = 'superadmin';
} elseif ($role === 'admin') {
    $_SESSION['user_id']      = 2;
    $_SESSION['username']     = 'admin_toko';
    $_SESSION['nama_lengkap'] = 'Admin Toko';
    $_SESSION['role']         = 'admin';
} elseif ($role === 'kasir') {
    $_SESSION['user_id']      = 4;
    $_SESSION['username']     = 'kasir_toko';
    $_SESSION['nama_lengkap'] = 'Kasir (Front Office)';
    $_SESSION['role']         = 'kasir';
} elseif ($role === 'pelanggan') {
    $_SESSION['user_id']      = 3;
    $_SESSION['username']     = 'pelanggan1';
    $_SESSION['nama_lengkap'] = 'Budi Santoso';
    $_SESSION['role']         = 'pelanggan';
}

$page = $_GET['page'] ?? 'index.php';
$filePath = __DIR__ . '/' . ltrim($page, '/');

if (file_exists($filePath)) {
    chdir(dirname($filePath));
    include $filePath;
} else {
    echo "File not found: " . htmlspecialchars($filePath);
}
