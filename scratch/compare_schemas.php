<?php
require __DIR__ . '/../config/koneksi.php';

echo "=== SHOW COLUMNS FROM penjualan IN ACTIVE DB ===\n";
$cols = $pdo->query("SHOW COLUMNS FROM penjualan")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}

echo "\n=== PENJUALAN TABLE IN demo 2.sql ===\n";
$sql = file_get_contents(__DIR__ . '/../database/isi/db_inventory (demo) 2.sql');
if (preg_match('/CREATE TABLE `penjualan` \((.*?)\) ENGINE/s', $sql, $m)) {
    echo $m[0];
}

