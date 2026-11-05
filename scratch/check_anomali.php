<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("SELECT * FROM log_anomali_fefo");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Total rows: " . count($rows) . "\n";
print_r($rows);
