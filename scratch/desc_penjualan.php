<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query("DESCRIBE penjualan");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}

