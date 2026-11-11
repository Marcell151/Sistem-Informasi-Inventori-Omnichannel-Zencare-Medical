<?php
require __DIR__ . '/../config/db.php';
$pdo = DB::connect();

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    $cols = $pdo->query("DESCRIBE $t")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        if (strpos($c['Field'], 'harga') !== false || strpos($c['Field'], 'beli') !== false) {
            echo "$t . {$c['Field']} ({$c['Type']})\n";
        }
    }
}

