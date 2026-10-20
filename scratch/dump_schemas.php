<?php
require_once __DIR__ . '/../config/koneksi.php';

$tables = [
    'users',
    'supplier',
    'pengaturan_web',
    'produk_induk',
    'produk_variasi',
    'stok_toko',
    'stok_batch',
    'unit_serial',
    'penerimaan_stok',
    'penerimaan_detail',
    'histori_konversi',
    'penjualan',
    'detail_penjualan',
    'kartu_stok',
    'log_anomali_fefo'
];

foreach ($tables as $t) {
    $row = $pdo->query("SHOW CREATE TABLE `$t`")->fetch(PDO::FETCH_ASSOC);
    echo "-- Schema for $t:\n" . $row['Create Table'] . ";\n\n";
}
