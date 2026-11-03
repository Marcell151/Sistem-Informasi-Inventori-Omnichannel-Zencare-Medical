<?php
require_once __DIR__ . '/../config/koneksi.php';

$pdo->beginTransaction();

// 1. Rename DCDEXP to DCD-27A01
$pdo->prepare("UPDATE stok_batch SET no_batch = REPLACE(no_batch, 'DCDEXP-2027-01', 'DCD-27A01') WHERE no_batch LIKE '%DCDEXP%'")->execute();
$pdo->prepare("UPDATE histori_konversi SET batch_asal = 'DCD-27A01', batch_hasil = 'DCD-27A01.1' WHERE batch_asal = 'DCDEXP-2027-01'")->execute();
$pdo->prepare("UPDATE kartu_stok SET keterangan = REPLACE(keterangan, 'DCDEXP-2027-01', 'DCD-27A01') WHERE keterangan LIKE '%DCDEXP%'")->execute();

// 2. Rename Retur SN
$snFix = [
    'OMR-TENS-RET1' => 'OMR-7120-R01',
    'OMR-TENS-RET2' => 'OMR-7120-R02',
    'OMR-TH-RET1'   => 'OMR-MC246-R01',
];
foreach ($snFix as $old => $new) {
    $pdo->prepare("UPDATE unit_serial SET serial_number = ? WHERE serial_number = ?")->execute([$new, $old]);
    $pdo->prepare("UPDATE detail_penjualan SET catatan_logistik = REPLACE(catatan_logistik, ?, ?) WHERE catatan_logistik LIKE ?")->execute([$old, $new, "%$old%"]);
    $pdo->prepare("UPDATE kartu_stok SET keterangan = REPLACE(keterangan, ?, ?) WHERE keterangan LIKE ?")->execute([$old, $new, "%$old%"]);
}

$pdo->commit();
echo "Cleaned up DCD and Retur SN.\n";

