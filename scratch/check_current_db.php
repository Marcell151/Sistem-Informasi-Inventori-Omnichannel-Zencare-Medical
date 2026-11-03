<?php
require __DIR__ . '/../config/koneksi.php';

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "=== TABEL-TABEL DI DATABASE AKTIF (db_inventory) ===\n";
foreach ($tables as $t) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo sprintf("%-30s : %d baris\n", $t, $count);
}

