<?php
session_start();
$_SESSION['user_id'] = 2;
$_SESSION['role'] = 'admin';
$_SESSION['nama_lengkap'] = 'Admin Toko';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

echo "Rendered length (Admin): " . strlen($html) . "\n";

// Check if tr has data-demo-fisik
preg_match('/<tr[^>]+data-id="[^"]+"[^>]*>/i', $html, $m);
echo "First TR (Admin): " . ($m[0] ?? 'Not found') . "\n";

// Check if Isi Cepat button is rendered
$hasBtn = strpos($html, 'isiOtomatisAngkaDemo');
echo "Has isiOtomatisAngkaDemo button (Admin): " . ($hasBtn !== false ? 'YES' : 'NO') . "\n";

