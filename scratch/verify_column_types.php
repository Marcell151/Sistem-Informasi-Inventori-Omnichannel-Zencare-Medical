<?php
require __DIR__ . '/../config/koneksi.php';

function checkColType($pdo, $tbl, $col) {
    $q = $pdo->query("SHOW COLUMNS FROM $tbl LIKE '$col'");
    $r = $q->fetch(PDO::FETCH_ASSOC);
    echo "$tbl.$col -> Type: {$r['Type']} | Null: {$r['Null']} | Default: {$r['Default']}\n";
}

checkColType($pdo, 'stok_batch', 'stok_sisa');
checkColType($pdo, 'stok_toko', 'stok');
checkColType($pdo, 'detail_opname_batch', 'stok_tersedia_sistem');
checkColType($pdo, 'detail_opname_batch', 'selisih');

