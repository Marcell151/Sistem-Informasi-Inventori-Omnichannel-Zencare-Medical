<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query('DESCRIBE unit_serial');
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$r['Field']} | {$r['Type']}\n";
}

echo "\n--- DISTINCT STATUS IN UNIT_SERIAL ---\n";
$stmt2 = $pdo->query('SELECT status, count(*) as cnt FROM unit_serial GROUP BY status');
while($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo "{$r['status']}: {$r['cnt']}\n";
}
