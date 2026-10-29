<?php
session_start();
$_SESSION['user_id'] = 4;
$_SESSION['role'] = 'kasir';
$_SESSION['nama_lengkap'] = 'Kasir Toko';

ob_start();
include __DIR__ . '/../pos/pos.php';
$html = ob_get_clean();
echo "pos/pos.php rendered! Length: " . strlen($html) . "\n";
echo "Has panel_batch_terlewat: " . (strpos($html, "panel_batch_terlewat") !== false ? "YES" : "NO") . "\n";
echo "Has Satuan Kecil (Eceran): " . (strpos($html, "Satuan Kecil (Eceran)") !== false ? "YES" : "NO") . "\n";
echo "Has Sudah Disesuaikan: " . (strpos($html, "Sudah Disesuaikan") !== false ? "YES" : "NO") . "\n";
