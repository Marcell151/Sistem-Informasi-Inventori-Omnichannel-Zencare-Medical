<?php
require __DIR__ . '/../config/koneksi.php';

echo "=== CEK RESERVED PER BATCH ===\n";
$stmt = $pdo->query("
    SELECT pba.id_batch, sb.no_batch, SUM(pba.qty_satuan_kecil) AS total_reserved
    FROM pesanan_batch_alokasi pba
    JOIN penjualan p ON pba.id_penjualan = p.id
    JOIN stok_batch sb ON pba.id_batch = sb.id
    WHERE p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
    GROUP BY pba.id_batch
");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Batches with active reservation: " . count($res) . "\n";
foreach ($res as $r) {
    echo "Batch ID: {$r['id_batch']} ({$r['no_batch']}) -> Reserved: {$r['total_reserved']}\n";
}

echo "\n=== CEK RESERVED PER ALKES VARIATION ===\n";
$stmt2 = $pdo->query("
    SELECT us.id_variasi, pv.nama_variasi, COUNT(us.id) AS total_reserved
    FROM unit_serial us
    JOIN penjualan p ON us.id_penjualan = p.id
    JOIN produk_variasi pv ON us.id_variasi = pv.id
    WHERE p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
    GROUP BY us.id_variasi
");
$res2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
echo "Alkes variations with active reservation: " . count($res2) . "\n";
foreach ($res2 as $r2) {
    echo "Var ID: {$r2['id_variasi']} ({$r2['nama_variasi']}) -> Reserved: {$r2['total_reserved']}\n";
}

