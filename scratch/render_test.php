<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'Superadmin IT';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();
file_put_contents(__DIR__ . '/rendered_stok_opname.html', $html);
echo "Rendered HTML length: " . strlen($html) . "\n";
