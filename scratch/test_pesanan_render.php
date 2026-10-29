<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['username'] = 'admin';
$_SESSION['nama_lengkap'] = 'Admin Gudang';

ob_start();
try {
    include 'admin/pesanan.php';
    $out = ob_get_clean();
    echo "admin/pesanan.php rendered successfully. Length: " . strlen($out) . "\n";
    // Check that NO duplicate invoice/label kirim button exists in the first td cell
    if (preg_match('/<td class="px-4 py-3.5">\s*<div class="flex items-center gap-2">\s*<svg.*?<\/td>/s', $out, $matches)) {
        if (strpos($matches[0], 'cetak_invoice') === false && strpos($matches[0], 'cetak_label_pengiriman') === false) {
            echo "[PASS] No misplaced buttons in NO INVOICE cell\n";
        } else {
            echo "[FAIL] Misplaced buttons found in NO INVOICE cell\n";
        }
    }
    // Check that action buttons exist in AKSI cell
    if (strpos($out, 'Invoice') !== false && strpos($out, 'Label Kirim') !== false) {
        echo "[PASS] Action buttons present in AKSI column\n";
    }
} catch (Throwable $e) {
    ob_end_clean();
    echo "[ERROR]: " . $e->getMessage() . "\n";
}
