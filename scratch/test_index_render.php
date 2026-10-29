<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['username'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'Superadmin ZenCare';

ob_start();
try {
    include 'index.php';
    $out = ob_get_clean();
    echo "Superadmin view rendered successfully. Output length: " . strlen($out) . "\n";
    if (strpos($out, 'disesuaikan') !== false) {
        echo "[PASS] Superadmin has 'disesuaikan' label\n";
    }
    if (strpos($out, 'openAnomaliModal') !== false) {
        echo "[PASS] Superadmin has openAnomaliModal\n";
    }
    if (strpos($out, 'renderAnomaliModalPage') !== false) {
        echo "[PASS] Superadmin has renderAnomaliModalPage\n";
    }
    if (strpos($out, 'renderReturModalPage') !== false) {
        echo "[PASS] Superadmin has renderReturModalPage\n";
    }
} catch (Throwable $e) {
    ob_end_clean();
    echo "[ERROR] Superadmin: " . $e->getMessage() . "\n";
}

$_SESSION['role'] = 'admin';
$_SESSION['username'] = 'admin';
$_SESSION['nama_lengkap'] = 'Admin Gudang';

ob_start();
try {
    include 'index.php';
    $outAdmin = ob_get_clean();
    echo "Admin view rendered successfully. Output length: " . strlen($outAdmin) . "\n";
    if (strpos($outAdmin, 'disesuaikan') !== false) {
        echo "[PASS] Admin has 'disesuaikan' label\n";
    }
} catch (Throwable $e) {
    ob_end_clean();
    echo "[ERROR] Admin: " . $e->getMessage() . "\n";
}
