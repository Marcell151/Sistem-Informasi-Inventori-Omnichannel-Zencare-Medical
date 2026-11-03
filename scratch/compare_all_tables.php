<?php
require __DIR__ . '/../config/koneksi.php';

$sql = file_get_contents(__DIR__ . '/../database/isi/db_inventory (demo) 2.sql');

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

echo "=== PERBANDINGAN KOLOM PER TABEL ===\n";
foreach ($tables as $t) {
    $dbCols = $pdo->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_COLUMN);
    
    // Cari CREATE TABLE di demo 2.sql
    if (preg_match('/CREATE TABLE `' . $t . '` \((.*?)\) ENGINE/s', $sql, $m)) {
        $lines = explode("\n", $m[1]);
        $demo2Cols = [];
        foreach ($lines as $l) {
            $l = trim($l);
            if (preg_match('/^`([a-zA-Z0-9_]+)`/', $l, $cm)) {
                $demo2Cols[] = $cm[1];
            }
        }
        $diffNew = array_diff($dbCols, $demo2Cols);
        $diffOld = array_diff($demo2Cols, $dbCols);
        if (!empty($diffNew) || !empty($diffOld)) {
            echo "Tabel `$t`:\n";
            if (!empty($diffNew)) echo "  + Kolom baru di DB aktif: " . implode(', ', $diffNew) . "\n";
            if (!empty($diffOld)) echo "  - Kolom hanya di demo 2: " . implode(', ', $diffOld) . "\n";
        } else {
            echo "Tabel `$t`: IDENTIK (" . count($dbCols) . " kolom)\n";
        }
    } else {
        echo "Tabel `$t`: TIDAK ADA DI demo 2.sql!\n";
    }
}

