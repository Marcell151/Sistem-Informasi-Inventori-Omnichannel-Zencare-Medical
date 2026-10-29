<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query("SELECT id, no_invoice, tipe_transaksi, status_pesanan FROM penjualan ORDER BY id DESC LIMIT 5");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);

