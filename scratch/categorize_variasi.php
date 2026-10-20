<?php
require_once __DIR__ . '/../config/koneksi.php';

$q = $pdo->query("
    SELECT pv.id, pv.id_produk_induk, pi.nama_produk, pv.nama_variasi, pv.sku_variasi,
           pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi, pi.kategori,
           pv.stok_minimum_besar, pv.stok_minimum_kecil, pv.harga_jual_kecil, pv.harga_jual_besar
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pv.id != 59
    ORDER BY pv.id
");
$all = $q->fetchAll(PDO::FETCH_ASSOC);
$obat = [];
$alkes = [];
foreach ($all as $item) {
    if ($item['kategori'] === 'Obat') {
        $obat[] = $item;
    } else {
        $alkes[] = $item;
    }
}
echo "Total Obat: " . count($obat) . " variasi\n";
echo "Total Alkes: " . count($alkes) . " variasi\n";
