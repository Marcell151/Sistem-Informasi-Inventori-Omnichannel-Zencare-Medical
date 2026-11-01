<?php
require_once __DIR__ . '/../config/koneksi.php';
$rows = $pdo->query("
    SELECT dob.id, dob.jenis_komoditas, pi.nama_produk, pv.nama_variasi, dob.stok_sistem_fisik,
           (SELECT COUNT(*) FROM unit_serial us WHERE us.id_variasi = pv.id) as sn_count,
           (SELECT COUNT(*) FROM stok_batch sb WHERE sb.id_variasi = pv.id) as batch_count
    FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE dob.id_sesi = 3 AND dob.jenis_komoditas = 'Alkes'
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $r) {
    echo "ID: {$r['id']} | {$r['nama_produk']} ({$r['nama_variasi']}) | SNs: {$r['sn_count']} | Batches: {$r['batch_count']} | Sistem Fisik: {$r['stok_sistem_fisik']}\n";
}

