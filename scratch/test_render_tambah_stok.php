<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'SuperAdmin (Pemilik)';
$_SESSION['id_cabang'] = 1;

ob_start();
include __DIR__ . '/../inventori/tambah_stok.php';
$html = ob_get_clean();

echo "Length of rendered HTML: " . strlen($html) . " bytes\n";
if (strpos($html, 'Terima Pengganti (Dibekukan)') !== false || strpos($html, 'Penerimaan Dibekukan') !== false) {
    echo "[PASS] Penerimaan & retur locked buttons verified.\n";
}

