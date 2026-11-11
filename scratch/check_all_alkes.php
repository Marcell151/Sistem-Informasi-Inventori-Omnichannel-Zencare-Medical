<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("
    SELECT pv.id AS id_variasi, pi.nama_produk, pv.nama_variasi, st.stok,
           (SELECT COUNT(*) FROM unit_serial us WHERE us.id_variasi = pv.id AND us.status = 'Tersedia') AS sn_count
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_toko st ON pv.id = st.id_variasi
    WHERE pi.kategori = 'Alat Kesehatan' AND pv.is_active = 1
");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID Var: {$r['id_variasi']} | {$r['nama_produk']} ({$r['nama_variasi']}) | Stok: {$r['stok']} | SN Tersedia: {$r['sn_count']}" . PHP_EOL;
}

