<?php
require 'config/koneksi.php';
$rows = $pdo->query("
    SELECT dob.jenis_komoditas, dob.satuan_tipe, dob.nama_satuan, dob.stok_sistem_fisik, dob.hitung_fisik, sb.no_batch, pi.nama_produk, pv.nama_variasi
    FROM detail_opname_batch dob
    LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE dob.id_sesi = 7
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $r) {
    echo "{$r['nama_produk']} | Batch: {$r['no_batch']} | Tipe: {$r['satuan_tipe']} | Satuan: {$r['nama_satuan']} | Sistem: {$r['stok_sistem_fisik']} | Fisik: {$r['hitung_fisik']}\n";
}

