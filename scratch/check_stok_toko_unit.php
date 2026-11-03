<?php
require_once __DIR__ . '/../config/koneksi.php';
$rows = $pdo->query("
    SELECT pv.id, pi.nama_produk, pv.nama_variasi, pv.rasio_konversi, st.stok AS stok_toko,
           (SELECT COALESCE(SUM(stok_sisa), 0) FROM stok_batch WHERE id_variasi = pv.id) AS total_batch
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    JOIN stok_toko st ON pv.id = st.id_variasi
    WHERE pi.kategori = 'Obat'
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $r) {
    echo "ID: {$r['id']} | {$r['nama_produk']} | Rasio: {$r['rasio_konversi']} | StokToko: {$r['stok_toko']} | TotalBatch: {$r['total_batch']}\n";
}

