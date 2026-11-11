<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("DESCRIBE detail_opname_sn");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}

