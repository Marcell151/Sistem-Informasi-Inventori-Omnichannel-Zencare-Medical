<?php
$kategori = $argv[1] ?? 'semua';
$kanal = $argv[2] ?? 'semua';

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'Superadmin';
$_GET = ['kategori' => $kategori, 'kanal' => $kanal];

ob_start();
include __DIR__ . '/../laporan/penjualan.php';
$html = ob_get_clean();

echo (strpos($html, 'Terjual Eceran (Kemasan Kecil)') !== false ? 'ADA' : 'TIDAK');

