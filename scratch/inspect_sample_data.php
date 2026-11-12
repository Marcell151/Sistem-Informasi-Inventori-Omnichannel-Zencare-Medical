<?php
require_once __DIR__ . '/../config/koneksi.php';

echo "1. Sample Invoices in penjualan:\n";
$stmt = $pdo->query("SELECT id, no_invoice, tipe_transaksi, total_akhir, status_pembayaran FROM penjualan ORDER BY id DESC LIMIT 5");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['id']}, Inv: {$r['no_invoice']}, Tipe: {$r['tipe_transaksi']}, Status: {$r['status_pembayaran']}, Total: Rp {$r['total_akhir']}\n";
}

echo "\n2. Sample Inbound in penerimaan_stok:\n";
$stmt2 = $pdo->query("SELECT id, no_faktur, tanggal_terima, total_nominal FROM penerimaan_stok ORDER BY id DESC LIMIT 5");
while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['id']}, Faktur: {$r['no_faktur']}, Tgl: {$r['tanggal_terima']}, Nominal: Rp {$r['total_nominal']}\n";
}

echo "\n3. Sample Closed Opname in sesi_opname:\n";
$stmt3 = $pdo->query("SELECT id, kode_sesi, status, dibekukan_pada FROM sesi_opname WHERE status = 'Selesai' ORDER BY id DESC LIMIT 3");
while ($r = $stmt3->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['id']}, Sesi: {$r['kode_sesi']}, Status: {$r['status']}\n";
}

