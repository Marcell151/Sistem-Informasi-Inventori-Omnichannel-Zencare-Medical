<?php
require __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query("SELECT pv.id, pv.nama_variasi, pi.nama_produk, st.stok,
                     (SELECT COUNT(*) FROM unit_serial WHERE id_variasi = pv.id AND status = 'Tersedia') as sn_tersedia,
                     (SELECT COUNT(*) FROM unit_serial WHERE id_variasi = pv.id AND status = 'Retur/Rusak') as sn_rusak,
                     (SELECT COUNT(*) FROM unit_serial WHERE id_variasi = pv.id AND status = 'Terjual') as sn_terjual
                     FROM produk_variasi pv
                     JOIN produk_induk pi ON pv.id_produk_induk = pi.id
                     JOIN stok_toko st ON st.id_variasi = pv.id
                     WHERE pi.kategori = 'Alat Kesehatan' AND pv.id IN (4, 5, 54, 56)");
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$r['nama_produk']} | stok_toko: {$r['stok']} | SN Tersedia: {$r['sn_tersedia']} | SN Retur/Rusak: {$r['sn_rusak']} | SN Terjual: {$r['sn_terjual']}\n";
}

