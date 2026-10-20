<?php
require 'config/koneksi.php';
$stmt = $pdo->prepare("UPDATE kartu_stok ks JOIN produk_variasi pv ON ks.id_variasi = pv.id SET ks.satuan_tipe = 'besar', ks.qty = ks.qty / pv.rasio_konversi WHERE ks.kanal = 'E-Commerce' AND ks.satuan_tipe = 'kecil' AND pv.rasio_konversi > 1 AND ks.qty >= pv.rasio_konversi");
$stmt->execute();
echo "Updated " . $stmt->rowCount() . " historical e-commerce kartu_stok rows.\n";
