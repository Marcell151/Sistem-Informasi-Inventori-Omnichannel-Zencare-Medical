<?php
require_once __DIR__ . '/../config/koneksi.php';

$res = $pdo->query("
    SELECT pv.id, pi.nama_produk, pv.nama_variasi, pi.kategori, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi, pv.harga_jual_kecil, pv.harga_jual_besar
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pi.kategori = 'Alat Kesehatan'
")->fetchAll(PDO::FETCH_ASSOC);
echo "Daftar Variasi Alat Kesehatan:\n";
print_r($res);

