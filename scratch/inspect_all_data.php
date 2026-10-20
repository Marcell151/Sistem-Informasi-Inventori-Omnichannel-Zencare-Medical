<?php
require_once __DIR__ . '/../config/koneksi.php';

function inspectTable($pdo, $name) {
    echo "==================== $name ====================\n";
    $rows = $pdo->query("SELECT * FROM `$name` LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
}

inspectTable($pdo, 'supplier');
inspectTable($pdo, 'pengaturan_web');
inspectTable($pdo, 'penerimaan_stok');
inspectTable($pdo, 'penerimaan_detail');
inspectTable($pdo, 'penjualan');
inspectTable($pdo, 'detail_penjualan');
inspectTable($pdo, 'histori_konversi');
inspectTable($pdo, 'kartu_stok');
inspectTable($pdo, 'unit_serial');
