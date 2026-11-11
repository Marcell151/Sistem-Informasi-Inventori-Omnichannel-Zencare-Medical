<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'SuperAdmin';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

echo "Has app-sidebar: " . (strpos($html, 'app-sidebar') !== false ? 'YES' : 'NO') . PHP_EOL;
echo "Has Isi Cepat Angka Uji Coba: " . (strpos($html, 'Isi Cepat Angka Uji Coba') !== false ? 'YES' : 'NO') . PHP_EOL;
echo "Has Simpan Hasil Hitung: " . (strpos($html, 'Simpan Hasil Hitung') !== false ? 'YES' : 'NO') . PHP_EOL;
echo "Has Tutup Penghitungan: " . (strpos($html, 'Tutup Penghitungan &amp; Buka Verifikasi') !== false ? 'YES' : 'NO') . PHP_EOL;
