<?php
require 'config/koneksi.php';
$batches = $pdo->query("
    SELECT sb.id, sb.no_batch, sb.stok_sisa, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi, pi.nama_produk
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE sb.no_batch LIKE '%.%'
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

echo "=== BATCH DENGAN TITIK (SUB-BATCH / ECERAN) ===\n";
print_r($batches);

$parentBatches = $pdo->query("
    SELECT sb.id, sb.no_batch, sb.stok_sisa, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi, pi.nama_produk
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE sb.no_batch NOT LIKE '%.%' AND pi.kategori != 'Alat Kesehatan' AND pv.rasio_konversi > 1
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

echo "=== BATCH TANPA TITIK (BATCH INDUK / DUS) ===\n";
print_r($parentBatches);

