<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_GET['no_invoice'] = 'INV-20261019-052';

ob_start();
try {
    include 'admin/cetak_label_pengiriman.php';
    $out = ob_get_clean();
    echo "SUCCESS: cetak_label_pengiriman.php rendered " . strlen($out) . " bytes\n";
    if (strpos($out, 'ZenCare Medical') !== false) echo "MATCH: ZenCare Medical\n";
    if (strpos($out, 'Toko Alat Kesehatan dan Obat') !== false) echo "MATCH: Toko Alat Kesehatan dan Obat\n";
    if (strpos($out, 'INV-20261019-052') !== false) echo "MATCH: Invoice number rendered\n";
    if (strpos($out, 'JsBarcode') !== false) echo "MATCH: JsBarcode rendered\n";
} catch (Throwable $e) {
    ob_end_clean();
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

