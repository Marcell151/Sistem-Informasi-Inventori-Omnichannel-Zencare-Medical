<?php
require_once __DIR__ . '/../config/koneksi.php';

// Check live DB
$liveStokToko = $pdo->query("SELECT SUM(stok) FROM stok_toko")->fetchColumn();
$liveStokBatch = $pdo->query("SELECT SUM(stok_sisa) FROM stok_batch")->fetchColumn();

echo "LIVE DATABASE:\n";
echo "- Total stok_toko: $liveStokToko\n";
echo "- Total stok_batch: $liveStokBatch\n\n";

// Check files
$f2 = 'c:/xampp/htdocs/inventory_zencare/database/isi/db_inventory (demo) 2.sql';
$f3 = 'c:/xampp/htdocs/inventory_zencare/database/isi/db_inventory (demo) 3.sql';

function analyzeSql($path) {
    if (!file_exists($path)) return "File not found";
    $c = file_get_contents($path);
    preg_match_all("/INSERT INTO `stok_toko`.*?VALUES\s*(.*?);/s", $c, $m1);
    preg_match_all("/INSERT INTO `stok_batch`.*?VALUES\s*(.*?);/s", $c, $m2);
    return [
        'size' => strlen($c),
        'stok_toko_inserts' => count($m1[0] ?? []),
        'stok_batch_inserts' => count($m2[0] ?? []),
    ];
}

echo "DEMO 2: \n";
print_r(analyzeSql($f2));
echo "DEMO 3: \n";
print_r(analyzeSql($f3));

