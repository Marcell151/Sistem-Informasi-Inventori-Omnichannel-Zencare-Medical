<?php
require __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->prepare("SELECT p.id, p.no_invoice, p.tipe_transaksi, p.metode_pengambilan, p.metode_bayar_pos, p.kurir, p.layanan FROM penjualan p WHERE p.no_invoice = ?");
$stmt->execute(['POS-20261029-6112']);
$res = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($res);
