<?php
require_once __DIR__ . '/../config/koneksi.php';

echo "Columns of pesanan_batch_alokasi:\n";
$cols = $pdo->query("SHOW COLUMNS FROM pesanan_batch_alokasi")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) echo "  - {$c['Field']}\n";

echo "Columns of stok_batch:\n";
$cols2 = $pdo->query("SHOW COLUMNS FROM stok_batch")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols2 as $c) echo "  - {$c['Field']}\n";

