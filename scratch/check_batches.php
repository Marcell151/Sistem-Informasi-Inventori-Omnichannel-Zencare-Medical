<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("
    SELECT sb.id, sb.id_variasi, pi.nama_produk, pi.kategori, pv.nama_variasi, sb.no_batch, sb.tgl_exp, sb.stok_sisa, pv.rasio_konversi, pv.satuan_kecil, pv.satuan_besar 
    FROM stok_batch sb 
    JOIN produk_variasi pv ON sb.id_variasi = pv.id 
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id 
    ORDER BY sb.id_variasi, sb.id
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "=== STOK BATCH DATA ===\n";
foreach ($rows as $r) {
    echo "ID: {$r['id']} | Prod: {$r['nama_produk']} ({$r['kategori']}) | Batch: {$r['no_batch']} | Sisa: {$r['stok_sisa']} | Rasio: {$r['rasio_konversi']} | Satuan: {$r['satuan_kecil']} / {$r['satuan_besar']}\n";
}

$stmt2 = $pdo->query("
    SELECT pv.id, pi.nama_produk, pi.kategori, pv.nama_variasi, pv.sku_variasi, pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, pv.harga_jual_kecil, pv.harga_jual_besar, st.stok
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_toko st ON st.id_variasi = pv.id
    ORDER BY pi.kategori, pv.id
");
$vps = $stmt2->fetchAll(PDO::FETCH_ASSOC);
echo "\n=== VARIASI PRODUK DATA ===\n";
foreach ($vps as $v) {
    echo "ID: {$v['id']} | Prod: {$v['nama_produk']} - {$v['nama_variasi']} | SKU: {$v['sku_variasi']} | Kat: {$v['kategori']} | Rasio: {$v['rasio_konversi']} | Satuan: '{$v['satuan_kecil']}' / '{$v['satuan_besar']}' | HK: {$v['harga_jual_kecil']} | HB: {$v['harga_jual_besar']} | Stok: {$v['stok']}\n";
}
