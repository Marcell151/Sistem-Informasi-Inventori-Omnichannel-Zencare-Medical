<?php
require __DIR__ . '/../config/koneksi.php';

echo "=== STOK BATCH CURRENT ===\n";
$batches = $pdo->query("SELECT sb.id, sb.id_variasi, pv.nama_variasi, pi.nama_produk, sb.no_batch, sb.tgl_exp, sb.stok_sisa FROM stok_batch sb JOIN produk_variasi pv ON sb.id_variasi = pv.id JOIN produk_induk pi ON pv.id_produk_induk = pi.id ORDER BY sb.tgl_exp ASC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($batches as $b) {
    echo "ID: {$b['id']} | [{$b['nama_produk']} - {$b['nama_variasi']}] | Batch: {$b['no_batch']} | Exp: {$b['tgl_exp']} | Sisa: {$b['stok_sisa']}\n";
}

echo "\n=== SESI OPNAME CURRENT ===\n";
$sesi = $pdo->query("SELECT * FROM sesi_opname ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($sesi as $s) {
    echo "ID: {$s['id']} | Kode: {$s['kode_sesi']} | Tipe: {$s['tipe_opname']} | Status: {$s['status']} | Tgl: {$s['tgl_mulai']} s/d {$s['tgl_selesai']}\n";
}

echo "\n=== PENJUALAN CURRENT ===\n";
$penjualan = $pdo->query("SELECT id, no_invoice, tipe_transaksi, status_pesanan, alasan_batal, dibatalkan_oleh, total_harga, created_at FROM penjualan ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($penjualan as $p) {
    echo "ID: {$p['id']} | Inv: {$p['no_invoice']} | Tipe: {$p['tipe_transaksi']} | Status: {$p['status_pesanan']} | Batal: {$p['alasan_batal']} ({$p['dibatalkan_oleh']}) | Total: {$p['total_harga']}\n";
}

