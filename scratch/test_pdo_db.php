<?php
require_once __DIR__ . '/../config/koneksi.php';

echo "Database name from PDO:\n";
$stmt = $pdo->query("SELECT DATABASE()");
echo $stmt->fetchColumn() . "\n";

echo "Check DROP TABLE test:\n";
$pdo->exec("DROP TABLE IF EXISTS test_table");
$pdo->exec("CREATE TABLE test_table (id int)");
$pdo->exec("INSERT INTO test_table VALUES (1)");
$s = $pdo->query("SELECT * FROM test_table");
echo "Count test_table: " . count($s->fetchAll()) . "\n";
$pdo->exec("DROP TABLE test_table");
echo "Dropped test_table successfully.\n";

