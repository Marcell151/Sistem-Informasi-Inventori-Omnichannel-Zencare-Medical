<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Total tables: " . count($tables) . "\n";
sort($tables);
foreach ($tables as $t) {
    echo "- $t\n";
}

