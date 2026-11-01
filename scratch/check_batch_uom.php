<?php
require 'config/koneksi.php';
$cols = $pdo->query('DESCRIBE stok_batch')->fetchAll(PDO::FETCH_ASSOC);
print_r($cols);

$sample = $pdo->query("
    SELECT sb.*, pv.nama_variasi, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
print_r($sample);

