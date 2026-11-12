<?php
require 'config/koneksi.php';
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Total Tables: " . count($tables) . PHP_EOL;
foreach ($tables as $t) {
    echo " - " . $t . PHP_EOL;
}
