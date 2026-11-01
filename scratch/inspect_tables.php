<?php
require 'config/koneksi.php';
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "=== LIST OF ALL TABLES ===\n";
foreach ($tables as $t) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo "- $t ($count rows)\n";
}

