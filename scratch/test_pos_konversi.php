<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';

require_once __DIR__ . '/../config/koneksi.php';

// Test konversi 1 box Antangin JRG (Var ID 26, Batch ANTJRG-2027-01)
$_POST['id_variasi'] = 26;
$_POST['batch_asal'] = 'ANTJRG-2027-01';

ob_start();
include __DIR__ . '/../api/pos_konversi.php';
$output = ob_get_clean();

echo "Response from pos_konversi.php:\n";
echo $output . "\n";

