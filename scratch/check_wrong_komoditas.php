<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->exec("
    UPDATE detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    SET dob.jenis_komoditas = 'Alkes'
    WHERE pi.kategori = 'Alat Kesehatan' AND dob.jenis_komoditas = 'Obat'
");

echo "Updated $stmt rows.\n";
