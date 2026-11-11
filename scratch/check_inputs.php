<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 4;
$_SESSION['role'] = 'kasir';
$_SESSION['nama_lengkap'] = 'Kasir Toko';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

// Check for the inputs of ID 1404 (Amoxicillin)
echo "input_baik_1404: " . (strpos($html, 'id="input_baik_1404"') !== false ? "YES" : "NO") . PHP_EOL;
echo "input_rusak_1404: " . (strpos($html, 'id="input_rusak_1404"') !== false ? "YES" : "NO") . PHP_EOL;
echo "input_exp_1404: " . (strpos($html, 'id="input_exp_1404"') !== false ? "YES" : "NO") . PHP_EOL;

// Check for Cefadroxil
echo "input_baik_1415: " . (strpos($html, 'id="input_baik_1415"') !== false ? "YES" : "NO") . PHP_EOL;
echo "input_rusak_1415: " . (strpos($html, 'id="input_rusak_1415"') !== false ? "YES" : "NO") . PHP_EOL;

// Check for Sanmol
echo "input_baik_1490: " . (strpos($html, 'id="input_baik_1490"') !== false ? "YES" : "NO") . PHP_EOL;
echo "input_exp_1490: " . (strpos($html, 'id="input_exp_1490"') !== false ? "YES" : "NO") . PHP_EOL;

