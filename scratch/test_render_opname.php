<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'SuperAdmin (Pemilik)';
$_SESSION['id_cabang'] = 1;

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$html = ob_get_clean();

echo "Length of rendered HTML: " . strlen($html) . " bytes\n";
if (strpos($html, 'Track SN') !== false) {
    echo "[PASS] Inline Track SN found in output.\n";
}
if (strpos($html, 'OMR-TENS-02') !== false) {
    echo "[PASS] Specific Serial Number chip found inline.\n";
}
if (strpos($html, 'modal_view_sn') === false) {
    echo "[PASS] Pop-up modal_view_sn successfully removed.\n";
}

