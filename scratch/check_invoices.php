<?php
require_once __DIR__ . '/../config/koneksi.php';
$invoices = [
    'WEB-20261028-544',
    'WEB-20261029-308',
    'WEB-20261101-670',
    'WEB-20261101-726',
    'WEB-20261103-677'
];
foreach ($invoices as $inv) {
    $row = $pdo->query("SELECT id, no_invoice, tipe_transaksi, status_pesanan FROM penjualan WHERE no_invoice = '$inv'")->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo "FOUND: $inv | {$row['tipe_transaksi']} | {$row['status_pesanan']}\n";
    } else {
        echo "NOT FOUND: $inv\n";
    }
}
