<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'Superadmin Test';
ob_start();
try {
    include 'index.php';
    $out = ob_get_clean();
    echo "SUCCESS: index.php rendered " . strlen($out) . " bytes\n";
    if (strpos($out, 'Anomali FEFO') !== false) {
        echo "MATCH: Anomali FEFO found in output\n";
    }
    if (strpos($out, 'Retur Supplier') !== false) {
        echo "MATCH: Retur Supplier found in output\n";
    }
    if (strpos($out, 'modalAnomaliDetail') !== false) {
        echo "MATCH: modalAnomaliDetail found in output\n";
    }
    if (strpos($out, 'modalReturDetail') !== false) {
        echo "MATCH: modalReturDetail found in output\n";
    }
} catch (Throwable $e) {
    ob_end_clean();
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

