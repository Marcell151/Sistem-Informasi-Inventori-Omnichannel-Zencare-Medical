<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 4;
$_SESSION['role'] = 'kasir';
$_SESSION['nama_lengkap'] = 'Kasir Toko';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

echo "Length: " . strlen($html) . PHP_EOL;
echo "Has Terminal Pencacahan Fisik: " . (strpos($html, 'Terminal Pencacahan Fisik') !== false ? 'YES' : 'NO') . PHP_EOL;
echo "Has Sidebar md:pl-64: " . (strpos($html, 'md:pl-64') !== false ? 'YES' : 'NO') . PHP_EOL;
echo "Has Isi Cepat Angka Uji Coba: " . (strpos($html, 'Isi Cepat Angka Uji Coba') !== false ? 'YES' : 'NO') . PHP_EOL;
echo "Has Simpan Hasil Hitung: " . (strpos($html, 'Simpan Hasil Hitung') !== false ? 'YES' : 'NO') . PHP_EOL;
echo "Has Belum Dihitung tab: " . (strpos($html, 'Belum Dihitung') !== false ? 'YES' : 'NO') . PHP_EOL;
echo "Has Sesi OPN-20261110-045: " . (strpos($html, 'OPN-20261110-045') !== false ? 'YES' : 'NO') . PHP_EOL;
echo "Has w-full flex-1: " . (strpos($html, 'w-full flex-1 min-h-screen bg-slate-50 flex flex-col min-w-0') !== false ? 'YES' : 'NO') . PHP_EOL;
