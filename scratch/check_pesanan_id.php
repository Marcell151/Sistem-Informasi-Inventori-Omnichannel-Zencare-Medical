<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query("SELECT id, no_invoice, tipe_transaksi, status_pesanan FROM penjualan WHERE tipe_transaksi = 'ecommerce' LIMIT 5");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['id']} | Inv: {$r['no_invoice']} | Status: {$r['status_pesanan']}\n";
}
