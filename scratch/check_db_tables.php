<?php
require_once 'config/koneksi.php';
$stmt = $pdo->query('SHOW TABLES');
echo "=== TABLES ===\n";
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo $row[0] . "\n";
}

echo "\n=== PENERIMAAN STOK COLS ===\n";
$cols = $pdo->query('DESCRIBE penerimaan_stok')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo $c['Field'] . " (" . $c['Type'] . ")\n";
}

$stmt = $pdo->query('SELECT * FROM penerimaan_detail WHERE qty_rusak > 0 LIMIT 5');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Rows with qty_rusak > 0: " . count($rows) . "\n";
foreach ($rows as $r) {
    print_r($r);
}
