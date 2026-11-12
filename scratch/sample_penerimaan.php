<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("DESCRIBE penerimaan_stok");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$r['Field']} ({$r['Type']})\n";
}
echo "\nSample rows:\n";
$s2 = $pdo->query("SELECT * FROM penerimaan_stok ORDER BY id DESC LIMIT 3");
while ($r = $s2->fetch(PDO::FETCH_ASSOC)) {
    print_r($r);
}
