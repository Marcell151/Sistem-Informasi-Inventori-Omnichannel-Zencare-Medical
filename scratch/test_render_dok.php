<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['username'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'SuperAdmin';

ob_start();
include __DIR__ . '/../inventori/dokumentasi_testing.php';
$out = ob_get_clean();
echo 'Render length: ' . strlen($out) . PHP_EOL;
echo 'Contains paper-sheet: ' . (strpos($out, 'paper-sheet') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'Contains border-rose-500: ' . (strpos($out, 'border-rose-500') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'Contains Uji Anomali: ' . (strpos($out, 'Uji Anomali') !== false ? 'YES' : 'NO') . PHP_EOL;

