<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("
    SELECT sb.id, pi.nama_produk, pv.nama_variasi, sb.no_batch, sb.tgl_exp, sb.stok_sisa, pv.rasio_konversi
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pi.nama_produk LIKE '%Sanmol%'
");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $isExp = (strtotime($r['tgl_exp']) < strtotime(date('Y-m-d')));
    echo "ID: {$r['id']} | {$r['nama_produk']} | Batch: {$r['no_batch']} | Exp: {$r['tgl_exp']} | isExp: " . ($isExp ? 'YES' : 'NO') . " | Stok: {$r['stok_sisa']}" . PHP_EOL;
}

