<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query('SELECT id, no_invoice, tipe_transaksi, status_pesanan, total_harga, created_at FROM penjualan ORDER BY id ASC');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

