<?php
require_once __DIR__ . '/../config/koneksi.php';

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "=== TABLES IN DATABASE ===\n";
foreach ($tables as $t) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo "- $t ($count rows)\n";
}

echo "\n=== USERS ===\n";
$users = $pdo->query("SELECT id, username, nama_lengkap, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
print_r($users);
