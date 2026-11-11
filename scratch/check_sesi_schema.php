<?php
require __DIR__ . '/../config/db.php';
$pdo = DB::connect();

echo "=== sesi_opname columns ===\n";
$stmt = $pdo->query('DESCRIBE sesi_opname');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' | ' . $row['Type'] . "\n";
}

