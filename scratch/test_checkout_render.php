<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'pembeli';
ob_start();
try {
    include 'zencare_checkout.php';
    $out = ob_get_clean();
    echo "SUCCESS: zencare_checkout.php rendered " . strlen($out) . " bytes\n";
    if (strpos($out, 'Pembayaran Otomatis Midtrans') !== false) {
        echo "MATCH: Pembayaran Otomatis Midtrans rendered\n";
    }
} catch (Throwable $e) {
    ob_end_clean();
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

