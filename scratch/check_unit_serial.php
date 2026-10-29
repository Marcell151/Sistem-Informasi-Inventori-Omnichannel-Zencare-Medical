<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("SELECT id_variasi, COUNT(*) as cnt, GROUP_CONCAT(serial_number) as sns FROM unit_serial GROUP BY id_variasi");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "=== UNIT SERIAL SUMMARY ===\n";
foreach ($rows as $r) {
    echo "Variasi ID: {$r['id_variasi']} | Count: {$r['cnt']} | SNs: {$r['sns']}\n";
}

