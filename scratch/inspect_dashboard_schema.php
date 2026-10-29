<?php
require 'config/koneksi.php';

echo "=== TABLES ===\n";
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    if (stripos($t, 'anomali') !== false || stripos($t, 'retur') !== false || stripos($t, 'fefo') !== false) {
        echo "- $t\n";
        $cols = $pdo->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo "    {$c['Field']} ({$c['Type']})\n";
        }
    }
}

