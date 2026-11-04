<?php
require_once __DIR__ . '/../config/koneksi.php';

echo "\n=== STRUKTUR TABEL DETAIL_PENJUALAN ===\n";
$dpCols = $pdo->query("SHOW COLUMNS FROM detail_penjualan")->fetchAll(PDO::FETCH_ASSOC);
foreach ($dpCols as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}

echo "\n=== CONTOH 5 DATA TERAKHIR DETAIL_PENJUALAN ===\n";
$sampleDp = $pdo->query("SELECT * FROM detail_penjualan ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($sampleDp);

