<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("DESCRIBE penjualan");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$r['Field']} ({$r['Type']})\n";
}
