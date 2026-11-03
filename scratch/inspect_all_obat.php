<?php
require __DIR__ . '/../config/koneksi.php';

$res = $pdo->query("
    SELECT pv.id AS id_variasi, pv.nama_variasi, pi.nama_produk, pv.rasio_konversi, pv.satuan_besar, pv.satuan_kecil,
           GROUP_CONCAT(CONCAT(sb.id, ':', sb.no_batch, ' (', sb.stok_sisa, ')') SEPARATOR ', ') AS batches
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_batch sb ON sb.id_variasi = pv.id
    WHERE pi.kategori = 'Obat'
    GROUP BY pv.id
    ORDER BY pv.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

echo "=== 41 VARIASI OBAT & BATCH-NYA ===\n";
foreach ($res as $r) {
    echo "ID: {$r['id_variasi']} | {$r['nama_produk']} ({$r['nama_variasi']}) | 1 {$r['satuan_besar']} = {$r['rasio_konversi']} {$r['satuan_kecil']} | Batches: {$r['batches']}\n";
}

