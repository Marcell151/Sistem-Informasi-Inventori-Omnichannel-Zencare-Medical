<?php
require_once __DIR__ . '/../config/koneksi.php';

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

$sql_content = file_get_contents(__DIR__ . '/../Demo_Bersih.sql');

echo "=== MEMERIKSA KESESUAIAN DEMO_BERSIH.SQL DENGAN LIVE DB ===\n";

foreach ($tables as $t) {
    // Get live columns
    $cols = $pdo->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_COLUMN);
    
    // Check if table exists in Demo_Bersih.sql
    if (!preg_match("/CREATE TABLE\s+[`\"]?" . preg_quote($t, '/') . "[`\"]?\s*\((.*?)\)\s*ENGINE/si", $sql_content, $matches)) {
        echo "[MISSING TABLE] $t tidak ditemukan di Demo_Bersih.sql\n";
        continue;
    }
    
    $table_def = $matches[1];
    $missing_cols = [];
    foreach ($cols as $c) {
        if (!preg_match("/[`\"]?" . preg_quote($c, '/') . "[`\"]?\s+/i", $table_def)) {
            $missing_cols[] = $c;
        }
    }
    
    if (!empty($missing_cols)) {
        echo "[KOLOM BEDA] Table $t kurang kolom: " . implode(", ", $missing_cols) . "\n";
    } else {
        echo "[SESUAI] Table $t (" . count($cols) . " kolom)\n";
    }
}
