<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

$stmt = $pdo->query("DESCRIBE produk_variasi");
echo "Columns in produk_variasi:\n";
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- {$r['Field']} ({$r['Type']})\n";
}

$stmt2 = $pdo->query("
    SELECT pv.id, pv.nama_variasi, pv.harga_beli, pv.harga_jual_kecil, pv.harga_jual_besar, pv.rasio_konversi
    FROM produk_variasi pv
    LIMIT 3
");
echo "\nSample produk_variasi:\n";
while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($r) . PHP_EOL;
}

