<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("SELECT dob.id, dob.id_sesi, dob.id_batch, dob.id_variasi, dob.jenis_komoditas, dob.satuan_tipe, dob.stok_sistem_fisik, sb.no_batch, pi.nama_produk
                     FROM detail_opname_batch dob
                     JOIN produk_variasi pv ON dob.id_variasi = pv.id
                     JOIN produk_induk pi ON pv.id_produk_induk = pi.id
                     LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
                     WHERE dob.id_variasi IN (51, 52, 48)
                     ORDER BY dob.id_sesi DESC, dob.id ASC");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
