<?php
require_once __DIR__ . '/../config/koneksi.php';

$rows = $pdo->query('
    SELECT pi.id, pi.nama_produk, pi.kategori, 
           COUNT(DISTINCT sb.id) as batch_count, 
           COUNT(DISTINCT us.id) as sn_count
    FROM produk_induk pi 
    JOIN produk_variasi pv ON pv.id_produk_induk = pi.id 
    LEFT JOIN stok_batch sb ON sb.id_variasi = pv.id 
    LEFT JOIN unit_serial us ON us.id_variasi = pv.id 
    GROUP BY pi.id, pi.nama_produk, pi.kategori
')->fetchAll(PDO::FETCH_ASSOC);

foreach($rows as $r) {
    echo "[{$r['kategori']}] {$r['nama_produk']} -> Batches: {$r['batch_count']}, SNs: {$r['sn_count']}\n";
}
