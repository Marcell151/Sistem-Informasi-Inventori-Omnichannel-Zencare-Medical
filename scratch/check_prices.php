<?php
require_once __DIR__ . '/../config/koneksi.php';

$res = $pdo->query("
    SELECT pv.id, pv.nama_variasi, pv.harga_jual_kecil, pv.harga_jual_besar, pv.rasio_konversi,
           (SELECT pd.harga_beli FROM penerimaan_detail pd WHERE pd.id_variasi = pv.id ORDER BY pd.id DESC LIMIT 1) AS last_harga_beli
    FROM produk_variasi pv
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
