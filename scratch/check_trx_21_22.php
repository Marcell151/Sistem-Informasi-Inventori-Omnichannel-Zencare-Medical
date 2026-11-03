<?php
require_once __DIR__ . '/../config/koneksi.php';
echo "=== PENJUALAN 21 & 22 ===\n";
$stmt = $pdo->query("SELECT * FROM detail_penjualan WHERE id_penjualan IN (21, 22)");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "=== ALOKASI BATCH 21 & 22 ===\n";
$stmt = $pdo->query("SELECT * FROM pesanan_batch_alokasi WHERE id_penjualan IN (21, 22)");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
