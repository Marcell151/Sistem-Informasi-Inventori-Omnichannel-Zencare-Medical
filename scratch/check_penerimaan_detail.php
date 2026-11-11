<?php
require __DIR__ . '/../config/db.php';
$pdo = DB::connect();

$stmt = $pdo->query("DESCRIBE penerimaan_detail");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['Field']} | {$row['Type']}\n";
}

