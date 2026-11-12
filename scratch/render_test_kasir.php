<?php
session_start();
$_SESSION['user_id'] = 4;
$_SESSION['role'] = 'kasir';
$_SESSION['nama_lengkap'] = 'Kasir (Front Office)';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

echo "Rendered length (Kasir): " . strlen($html) . "\n";

// Check if tr has data-demo-fisik
preg_match('/<tr[^>]+data-id="[^"]+"[^>]*>/i', $html, $m);
echo "First TR (Kasir): " . ($m[0] ?? 'Not found') . "\n";

// Check if Isi Cepat button is rendered
$hasBtn = strpos($html, 'isiOtomatisAngkaDemo');
echo "Has isiOtomatisAngkaDemo button (Kasir): " . ($hasBtn !== false ? 'YES' : 'NO') . "\n";

