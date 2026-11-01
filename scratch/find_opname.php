<?php
require __DIR__ . '/../config/koneksi.php';
try {
    $pdo->exec("ALTER TABLE detail_opname_batch MODIFY COLUMN id_batch INT NULL");
    echo "SUCCESS: detail_opname_batch.id_batch is now NULLABLE.\n";
} catch(Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
