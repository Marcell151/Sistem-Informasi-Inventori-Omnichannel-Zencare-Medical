<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

$stmt = $pdo->query("DESCRIBE penerimaan_detail");
echo "Columns in penerimaan_detail:\n";
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- {$r['Field']} ({$r['Type']})\n";
}

$stmt2 = $pdo->query("
    SELECT pd.id, pd.id_variasi, pd.harga_beli, pd.subtotal
    FROM penerimaan_detail pd
    ORDER BY pd.id DESC
    LIMIT 5
");
echo "\nSample penerimaan_detail:\n";
while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($r) . PHP_EOL;
}

