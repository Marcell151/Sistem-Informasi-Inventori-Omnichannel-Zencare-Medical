<?php
require __DIR__ . '/../config/db.php';
$pdo = DB::connect();

echo "=== detail_opname_batch columns ===\n";
$stmt = $pdo->query('DESCRIBE detail_opname_batch');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' | ' . $row['Type'] . "\n";
}

echo "\n=== produk_variasi price columns ===\n";
$stmt = $pdo->query('DESCRIBE produk_variasi');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (strpos($row['Field'], 'harga') !== false) {
        echo $row['Field'] . ' | ' . $row['Type'] . "\n";
    }
}

echo "\n=== stok_batch columns ===\n";
$stmt = $pdo->query('DESCRIBE stok_batch');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (strpos($row['Field'], 'harga') !== false || strpos($row['Field'], 'exp') !== false) {
        echo $row['Field'] . ' | ' . $row['Type'] . "\n";
    }
}
