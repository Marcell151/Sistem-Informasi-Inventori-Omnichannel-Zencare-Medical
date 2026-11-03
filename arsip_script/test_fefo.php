<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->prepare("SELECT v.id, v.sku_variasi, i.nama_produk, i.kategori, 
(SELECT GROUP_CONCAT(no_batch ORDER BY tgl_exp ASC SEPARATOR ',') FROM stok_batch WHERE id_variasi = v.id AND stok_sisa > 0) AS batch_list 
FROM produk_variasi v JOIN produk_induk i ON v.id_produk_induk=i.id WHERE v.sku_variasi = 'AMX-500'");
$stmt->execute();
print_r($stmt->fetch(PDO::FETCH_ASSOC));
?>
