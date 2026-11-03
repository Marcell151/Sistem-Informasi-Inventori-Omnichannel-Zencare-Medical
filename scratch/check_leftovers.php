<?php
require_once __DIR__ . '/../config/koneksi.php';
$leftBatches = $pdo->query("SELECT id, no_batch FROM stok_batch WHERE no_batch LIKE '%EXP%' OR no_batch LIKE '%OLD%'")->fetchAll(PDO::FETCH_ASSOC);
print_r($leftBatches);

$leftSn = $pdo->query("SELECT id, serial_number FROM unit_serial WHERE serial_number LIKE 'OMR-TENS%' OR serial_number LIKE 'OMR-TH%' OR serial_number LIKE 'IRTHER%'")->fetchAll(PDO::FETCH_ASSOC);
print_r($leftSn);

