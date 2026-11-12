<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'SuperAdmin (Pemilik)';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

echo "Rendered length: " . strlen($html) . "\n";

// Check if tr has data-demo-fisik or data-stok-sistem
preg_match('/<tr[^>]+data-id="[^"]+"[^>]*>/i', $html, $m);
echo "First TR: " . ($m[0] ?? 'Not found') . "\n";

// Check input_baik_
preg_match('/<input[^>]+id="input_baik_[^"]+"[^>]*>/i', $html, $m2);
echo "First input_baik: " . ($m2[0] ?? 'Not found') . "\n";

