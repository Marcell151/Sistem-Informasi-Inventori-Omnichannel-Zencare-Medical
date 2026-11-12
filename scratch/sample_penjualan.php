<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("SELECT id, no_invoice, tipe_transaksi, total_harga, status_pesanan FROM penjualan ORDER BY id DESC LIMIT 5");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['id']}, Inv: {$r['no_invoice']}, Tipe: {$r['tipe_transaksi']}, Status: {$r['status_pesanan']}, Total: Rp {$r['total_harga']}\n";
}

