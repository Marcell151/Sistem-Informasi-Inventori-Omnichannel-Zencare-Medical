<?php
// File: cetak_invoice.php (Root Redirector)
$queryString = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
header("Location: pos/cetak_invoice.php" . $queryString);
exit;
?>
