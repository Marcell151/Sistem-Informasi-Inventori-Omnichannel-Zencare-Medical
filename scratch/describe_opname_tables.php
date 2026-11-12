<?php
require 'config/koneksi.php';
$opname_tables = ['sesi_opname', 'detail_opname_batch', 'detail_opname_sn', 'riwayat_hitung_opname'];
foreach ($opname_tables as $t) {
    echo "\n==================== Table: $t ====================\n";
    $stmt = $pdo->query("DESCRIBE $t");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        printf("%-25s %-20s %-8s %-8s %-15s\n", $c['Field'], $c['Type'], $c['Null'], $c['Key'], $c['Default'] ?? 'NULL');
    }
}

