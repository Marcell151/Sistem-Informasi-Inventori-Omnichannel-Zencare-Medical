<?php
require_once __DIR__ . '/../config/koneksi.php';

// Count obat batches with stock > 0
$sqlObat = "
    SELECT COUNT(*) as c
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pv.is_active = 1 
      AND (sb.stok_sisa + COALESCE((
               SELECT SUM(pba.qty_satuan_kecil) 
               FROM pesanan_batch_alokasi pba 
               JOIN penjualan p ON pba.id_penjualan = p.id 
               WHERE pba.id_batch = sb.id 
                 AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
           ), 0)) > 0
";
$cntObat = $pdo->query($sqlObat)->fetchColumn();

// Count alkes with SN > 0
$sqlAlkes = "
    SELECT COUNT(*) as c
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pv.is_active = 1
      AND ((SELECT COUNT(*) FROM unit_serial us WHERE us.id_variasi = pv.id AND us.status = 'Tersedia') + 
           (SELECT COUNT(*) FROM unit_serial us JOIN penjualan p ON us.id_penjualan = p.id WHERE us.id_variasi = pv.id AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil'))) > 0
";
$cntAlkes = $pdo->query($sqlAlkes)->fetchColumn();

echo "Snapshot Obat/BMHP Batches: $cntObat\n";
echo "Snapshot Alkes SN Products: $cntAlkes\n";
echo "Total Snapshot Rows: " . ($cntObat + $cntAlkes) . "\n";

