<?php
require_once 'config/koneksi.php';
\ = \->prepare("SELECT v.id, v.sku_variasi, i.nama_produk, i.kategori, 
(SELECT GROUP_CONCAT(no_batch ORDER BY tgl_exp ASC SEPARATOR ',') FROM stok_batch WHERE id_variasi = v.id AND stok_sisa > 0) AS batch_list 
FROM produk_variasi v JOIN produk_induk i ON v.id_produk_induk=i.id WHERE v.sku_variasi = 'AMX-500'");
\->execute();
print_r(\->fetch(PDO::FETCH_ASSOC));
?>
