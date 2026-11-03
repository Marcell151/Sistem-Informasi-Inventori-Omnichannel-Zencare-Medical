<?php
require_once __DIR__ . '/../config/koneksi.php';
$batches = $pdo->query("SELECT * FROM stok_batch WHERE id_variasi = 1")->fetchAll(PDO::FETCH_ASSOC);
print_r($batches);

