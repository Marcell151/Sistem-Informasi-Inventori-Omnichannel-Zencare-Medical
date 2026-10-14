<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query("SELECT pv.id, pi.nama_produk, pv.nama_variasi, sc.stok, pv.rasio_konversi, FLOOR(sc.stok/pv.rasio_konversi) as dus FROM produk_variasi pv JOIN produk_induk pi ON pi.id=pv.id_produk_induk LEFT JOIN stok_toko sc ON sc.id_variasi=pv.id WHERE FLOOR(sc.stok/pv.rasio_konversi) BETWEEN 50 AND 65 OR sc.stok BETWEEN 50 AND 65");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));


