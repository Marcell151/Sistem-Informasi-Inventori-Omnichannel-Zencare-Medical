<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 4;
$_SESSION['role'] = 'kasir';
$_SESSION['nama_lengkap'] = 'Kasir Toko';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

// Use regex to find all <tr data-id="...">
preg_match_all('/<tr\s+[^>]*data-id="(\d+)"[^>]*>/', $html, $matches);
echo "Total tr with data-id: " . count($matches[0]) . PHP_EOL;

foreach (array_slice($matches[0], 0, 15) as $tr) {
    echo $tr . PHP_EOL;
}

