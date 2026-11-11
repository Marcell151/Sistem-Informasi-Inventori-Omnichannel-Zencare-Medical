<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

$stmt = $pdo->query("SELECT DISTINCT kategori, COUNT(*) as cnt FROM produk_induk GROUP BY kategori");
echo "Kategori di produk_induk:\n";
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- '{$r['kategori']}': {$r['cnt']} produk\n";
}

$stmt2 = $pdo->query("
    SELECT pi.id, pi.nama_produk, pi.kategori, COUNT(sb.id) as batch_count, COUNT(us.id) as sn_count
    FROM produk_induk pi
    JOIN produk_variasi pv ON pi.id = pv.id_produk_induk
    LEFT JOIN stok_batch sb ON pv.id = sb.id_variasi
    LEFT JOIN unit_serial us ON pv.id = us.id_variasi
    WHERE pi.kategori = 'Alat Kesehatan'
    GROUP BY pi.id
");
echo "\nProduk dengan kategori 'Alat Kesehatan':\n";
while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['id']} | {$r['nama_produk']} | Batches: {$r['batch_count']} | SNs: {$r['sn_count']}\n";
}

