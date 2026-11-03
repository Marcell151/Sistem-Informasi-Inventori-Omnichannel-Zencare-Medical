<?php
require __DIR__ . '/../config/koneksi.php';

$dDb = $pdo->query("SHOW COLUMNS FROM detail_opname_batch LIKE 'alasan_mutasi'")->fetch(PDO::FETCH_ASSOC);
echo "DB detail_opname_batch.alasan_mutasi: {$dDb['Type']}\n";

