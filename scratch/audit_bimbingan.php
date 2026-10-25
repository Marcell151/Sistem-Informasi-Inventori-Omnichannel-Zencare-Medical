<?php
require __DIR__ . '/../config/koneksi.php';

$st = $pdo->query("SELECT pi.id, pi.nama_produk, pi.kategori, pv.id as var_id, pv.nama_variasi, pv.sku_variasi, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi FROM produk_induk pi JOIN produk_variasi pv ON pi.id = pv.id_produk_induk WHERE pi.kategori = 'Obat' LIMIT 15");
while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['var_id']} | {$r['nama_produk']} - {$r['nama_variasi']} | SKU: {$r['sku_variasi']} | Satuan: {$r['satuan_besar']} (1:{$r['rasio_konversi']} {$r['satuan_kecil']})\n";
}
