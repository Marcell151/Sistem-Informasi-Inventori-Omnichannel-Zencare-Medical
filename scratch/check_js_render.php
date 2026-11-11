<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 4;
$_SESSION['role'] = 'kasir';
$_SESSION['nama_lengkap'] = 'Kasir Toko';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

// Check if isiOtomatisAngkaDemo is present in the rendered HTML
if (preg_match('/function isiOtomatisAngkaDemo\(\)\s*\{(.*?)\n\}/s', $html, $m)) {
    echo "Found isiOtomatisAngkaDemo in rendered HTML!" . PHP_EOL;
    echo substr($m[0], 0, 500) . "..." . PHP_EOL;
} else {
    echo "NOT FOUND in rendered HTML!" . PHP_EOL;
}

