<?php
require 'config/koneksi.php';
$stmt = $pdo->query("SELECT ks.*, pv.sku_variasi FROM kartu_stok ks JOIN produk_variasi pv ON ks.id_variasi = pv.id WHERE ks.no_ref_dokumen LIKE 'WEB%' OR ks.keterangan LIKE '%WEB%' ORDER BY ks.id DESC");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
