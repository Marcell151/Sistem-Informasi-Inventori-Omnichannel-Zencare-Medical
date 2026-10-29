<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

$pdo->exec("ALTER TABLE log_anomali_fefo CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
echo "Collation aligned to utf8mb4_general_ci!\n";

