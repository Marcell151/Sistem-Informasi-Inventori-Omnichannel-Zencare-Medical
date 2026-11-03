<?php
require __DIR__ . '/../config/koneksi.php';
$col = $pdo->query("SHOW COLUMNS FROM kartu_stok LIKE 'alasan_mutasi'")->fetch(PDO::FETCH_ASSOC);
print_r($col);

