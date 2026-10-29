<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'Kasir Test';
$_SESSION['id_cabang'] = 1;
ob_start();
try {
    include 'pos/pos.php';
    $out = ob_get_clean();
    echo "SUCCESS: pos.php rendered " . strlen($out) . " bytes\n";
    if (strpos($out, 'Shift FEFO:') !== false) echo "MATCH: Shift FEFO pill found\n";
    if (strpos($out, 'openKasirAnomaliModal') !== false) echo "MATCH: openKasirAnomaliModal found\n";
} catch (Throwable $e) {
    ob_end_clean();
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

