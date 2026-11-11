<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

$stmt = $pdo->query('
    SELECT dob.id, dob.id_sesi, dob.id_batch, dob.id_variasi, dob.jenis_komoditas, dob.satuan_tipe, dob.nama_satuan, dob.stok_sistem_fisik, pi.nama_produk, pi.kategori
    FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE dob.id_sesi = 28 AND (pi.nama_produk LIKE "%Asam Urat%" OR pi.nama_produk LIKE "%Kolesterol%" OR pi.nama_produk LIKE "%Alkohol%")
');
while($r = $stmt->fetch()) {
    echo $r['id'] . ' | ' . $r['nama_produk'] . ' | id_batch=' . var_export($r['id_batch'], true) . ' | jenis=' . $r['jenis_komoditas'] . ' | stok=' . $r['stok_sistem_fisik'] . PHP_EOL;
}

