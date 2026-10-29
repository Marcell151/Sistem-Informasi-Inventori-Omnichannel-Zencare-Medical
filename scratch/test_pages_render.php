<?php
require_once __DIR__ . '/../config/koneksi.php';

echo "Testing DB query directly...\n";
$rows = $pdo->query("SELECT * FROM log_anomali_fefo")->fetchAll();
echo "Found " . count($rows) . " rows in log_anomali_fefo\n";
