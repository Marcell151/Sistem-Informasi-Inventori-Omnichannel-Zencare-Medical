<?php
session_start();
$_SESSION['nama_lengkap'] = 'SuperAdmin (Pemilik)';
$_SESSION['role'] = 'superadmin';
$_GET['no_invoice'] = 'WEB-20261101-726';
$_GET['format'] = 'a4';
ob_start();
require __DIR__ . '/../pos/cetak_invoice.php';
$output = ob_get_clean();

echo "=== TEST E-COMMERCE WITH SUPERADMIN SESSION ===\n";
if (preg_match('/Admin \/ Petugas Logistik: <strong>(.*?)<\/strong>/s', $output, $m)) {
    echo "Petugas di Card: " . trim($m[1]) . "\n";
}
if (preg_match('/Petugas Logistik & Admin Toko.*?text-decoration: underline.*?>(.*?)<\/div>/s', $output, $m)) {
    echo "Penanggung Jawab di TTD: " . trim($m[1]) . "\n";
}
