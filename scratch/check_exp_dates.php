<?php
require 'config/koneksi.php';

echo "=== STOK BATCH DENGAN TGL EXP TERDEKAT ===\n";
$batches = $pdo->query("
    SELECT sb.id, sb.id_variasi, sb.no_batch, sb.tgl_exp, sb.stok_sisa, pv.nama_variasi, pi.nama_produk
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    ORDER BY sb.tgl_exp ASC
    LIMIT 15
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($batches as $b) {
    echo "ID: {$b['id']} | {$b['nama_produk']} ({$b['nama_variasi']}) | Batch: {$b['no_batch']} | Exp: {$b['tgl_exp']} | Stok: {$b['stok_sisa']}\n";
}

