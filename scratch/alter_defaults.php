<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$pdo->exec("ALTER TABLE detail_opname_batch MODIFY fisik_baik INT(11) NULL DEFAULT NULL");
$pdo->exec("ALTER TABLE detail_opname_batch MODIFY fisik_rusak INT(11) NULL DEFAULT NULL");
$pdo->exec("ALTER TABLE detail_opname_batch MODIFY fisik_expired INT(11) NULL DEFAULT NULL");

echo "Columns fisik_baik, fisik_rusak, fisik_expired modified to NULL DEFAULT NULL.\n";

$cols = $pdo->query("DESCRIBE detail_opname_batch")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    if (in_array($c['Field'], ['fisik_baik', 'fisik_rusak', 'fisik_expired'])) {
        echo "{$c['Field']} -> Type: {$c['Type']}, Null: {$c['Null']}, Default: " . var_export($c['Default'], true) . "\n";
    }
}

