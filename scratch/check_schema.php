<?php
require_once __DIR__ . '/../config/koneksi.php';
$rows = $pdo->query("SELECT pv.id, pi.nama_produk, pv.nama_variasi, pi.kategori, pv.rasio_konversi, pv.satuan_besar, pv.satuan_kecil, pv.harga_jual_besar, pv.harga_jual_kecil FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk=pi.id WHERE pi.kategori='Alat Kesehatan'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "ID: {$r['id']} | {$r['nama_produk']} | Rasio: {$r['rasio_konversi']} | Besar: {$r['satuan_besar']} | Kecil: {$r['satuan_kecil']} | HrgB: {$r['harga_jual_besar']} | HrgK: {$r['harga_jual_kecil']}\n";
}
