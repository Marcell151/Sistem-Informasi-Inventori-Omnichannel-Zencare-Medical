<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$totalProduk = $pdo->query('SELECT COUNT(*) FROM produk_induk')->fetchColumn();
$totalVar = $pdo->query('SELECT COUNT(*) FROM produk_variasi WHERE is_active=1')->fetchColumn();
$totalBatch = $pdo->query('SELECT COUNT(*) FROM stok_batch WHERE stok_sisa>0')->fetchColumn();
$totalSn = $pdo->query('SELECT COUNT(*) FROM unit_serial')->fetchColumn();
$totalStokToko = $pdo->query('SELECT COUNT(*) FROM stok_toko WHERE stok>0')->fetchColumn();

echo "Total Produk Induk: $totalProduk\n";
echo "Total Variasi Aktif: $totalVar\n";
echo "Total Stok Batch Aktif: $totalBatch\n";
echo "Total Serial Number: $totalSn\n";
echo "Total Stok Toko Aktif: $totalStokToko\n";

$batches = $pdo->query("
    SELECT sb.id, pi.nama_produk, pv.nama_variasi, sb.no_batch, sb.stok_sisa, sb.tgl_exp, pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pv.is_active = 1 AND sb.stok_sisa > 0
    ORDER BY pi.nama_produk ASC
")->fetchAll(PDO::FETCH_ASSOC);

echo "\n--- DAFTAR BATCH OBAT AKTIF (" . count($batches) . " batch) ---\n";
foreach ($batches as $b) {
    echo "ID: {$b['id']} | {$b['nama_produk']} - {$b['nama_variasi']} | Batch: {$b['no_batch']} | Sisa: {$b['stok_sisa']} {$b['satuan_kecil']} | Exp: {$b['tgl_exp']}\n";
}

$alkes = $pdo->query("
    SELECT pv.id, pi.nama_produk, pv.nama_variasi, st.stok,
           (SELECT COUNT(*) FROM unit_serial WHERE id_variasi = pv.id AND status = 'Tersedia') as total_sn
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_toko st ON pv.id = st.id_variasi
    WHERE pi.kategori = 'Alat Kesehatan' AND pv.is_active = 1
")->fetchAll(PDO::FETCH_ASSOC);

echo "\n--- DAFTAR ALAT KESEHATAN AKTIF (" . count($alkes) . " variasi) ---\n";
foreach ($alkes as $a) {
    echo "ID Var: {$a['id']} | {$a['nama_produk']} - {$a['nama_variasi']} | Stok Toko: {$a['stok']} | Total SN Tersedia: {$a['total_sn']}\n";
}

