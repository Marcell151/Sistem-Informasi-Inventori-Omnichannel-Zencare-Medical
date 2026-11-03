<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query("SELECT id, id_variasi, no_batch, tgl_exp, stok_sisa FROM stok_batch WHERE id_variasi IN (1, 2, 7, 26)");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
