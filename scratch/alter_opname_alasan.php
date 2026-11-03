<?php
require __DIR__ . '/../config/koneksi.php';
$pdo->exec("ALTER TABLE detail_opname_batch MODIFY COLUMN alasan_mutasi VARCHAR(255) NULL");
echo "detail_opname_batch.alasan_mutasi modified to VARCHAR(255) successfully.\n";

