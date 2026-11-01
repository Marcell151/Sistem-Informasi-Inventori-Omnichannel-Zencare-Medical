<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$pdo->exec("UPDATE produk_variasi SET is_active = 0 WHERE id IN (61, 62, 63)");
$cnt = $pdo->query("SELECT COUNT(*) FROM produk_variasi WHERE is_active = 1")->fetchColumn();
echo "Active variations now: $cnt\n";

