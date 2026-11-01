<?php
require __DIR__ . '/../config/koneksi.php';

echo "=== CEK TABEL DAN KOLOM TERKAIT RETUR ===\n";
$tables = ['penerimaan_stok', 'penerimaan_detail', 'unit_serial', 'kartu_stok', 'detail_penjualan', 'penjualan'];
foreach ($tables as $t) {
    echo "--- $t ---\n";
    $q = $pdo->query("SHOW COLUMNS FROM $t");
    while($r = $q->fetch(PDO::FETCH_ASSOC)) {
        if (stripos($r['Field'], 'retur') !== false || stripos($r['Type'], 'retur') !== false || stripos($r['Field'], 'status') !== false) {
            echo "  {$r['Field']} | {$r['Type']} | {$r['Default']}\n";
        }
    }
}

