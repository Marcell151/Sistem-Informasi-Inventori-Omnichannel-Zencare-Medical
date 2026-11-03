<?php
require __DIR__ . '/../config/koneksi.php';

$ksDb = $pdo->query("SHOW COLUMNS FROM kartu_stok LIKE 'alasan_mutasi'")->fetch(PDO::FETCH_ASSOC);
echo "DB kartu_stok.alasan_mutasi: {$ksDb['Type']}\n";

$sql = file_get_contents(__DIR__ . '/../database/isi/db_inventory (demo) 2.sql');
if (preg_match('/`alasan_mutasi` (enum\(.*?\))/', $sql, $m)) {
    echo "demo 2 alasan_mutasi: {$m[1]}\n";
}

