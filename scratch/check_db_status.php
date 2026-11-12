<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query("DESCRIBE sesi_opname");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Columns of sesi_opname:\n";
foreach ($cols as $c) {
    echo "- {$c['Field']} ({$c['Type']})\n";
}

$stmt2 = $pdo->query("SELECT * FROM sesi_opname ORDER BY 1 DESC");
$rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
echo "\nTotal sesi: " . count($rows) . "\n";
foreach ($rows as $r) {
    print_r($r);
}
