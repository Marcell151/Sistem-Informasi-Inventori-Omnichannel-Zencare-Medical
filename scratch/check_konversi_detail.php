<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

// Ambil contoh produk yang pernah dikonversi
$q = $pdo->query("
    SELECT pi.nama_produk, pv.nama_variasi, pv.rasio_konversi, pv.satuan_besar, pv.satuan_kecil,
           sb.no_batch, sb.stok_sisa
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE sb.no_batch LIKE '%.%'
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

echo "Contoh Batch Hasil Konversi (Satuan Kecil):\n";
foreach ($q as $row) {
    // Cari batch induk asalnya
    $parentBatchNo = explode('.', $row['no_batch'])[0];
    $parent = $pdo->query("SELECT no_batch, stok_sisa FROM stok_batch WHERE no_batch = '$parentBatchNo'")->fetch(PDO::FETCH_ASSOC);
    
    $sisaDusUtuh = floor($parent['stok_sisa'] / $row['rasio_konversi']);
    echo "- {$row['nama_produk']} ({$row['nama_variasi']}):\n";
    echo "  * Batch Induk (Satuan Besar): {$parent['no_batch']} -> Sisa {$parent['stok_sisa']} pcs = {$sisaDusUtuh} {$row['satuan_besar']} (Hanya ini yang dihitung di Stok Satuan Besar)\n";
    echo "  * Sub-Batch Konversi (Satuan Kecil): {$row['no_batch']} -> {$row['stok_sisa']} {$row['satuan_kecil']} (Masuk ke Stok Satuan Kecil)\n\n";
}

