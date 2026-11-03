<?php
require __DIR__ . '/../config/koneksi.php';
$cols = $pdo->query("SHOW COLUMNS FROM detail_opname_batch")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "{$c['Field']} - {$c['Type']} - {$c['Null']} - Default: {$c['Default']}\n";
}

