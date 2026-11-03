<?php
require_once __DIR__ . '/../config/koneksi.php';
$q = $pdo->query("SELECT pv.*, pi.nama_produk, pi.kategori 
                  FROM produk_variasi pv 
                  JOIN produk_induk pi ON pv.id_produk_induk = pi.id 
                  WHERE pv.id = 51")->fetch(PDO::FETCH_ASSOC);

echo "Rasio: " . $q['rasio_konversi'] . "\n";
if ($q['rasio_konversi'] > 1) {
    echo "Renders: DUAL (Eceran & Grosir)\n";
} else {
    echo "Renders: SINGLE (Harga Satuan)\n";
}
