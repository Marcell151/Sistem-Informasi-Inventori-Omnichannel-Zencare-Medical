<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

$stmt = $pdo->query("
    SELECT pi.id, pi.nama_produk, pi.kategori, pv.id as id_variasi, pv.nama_variasi, pv.sku_variasi
    FROM produk_induk pi
    JOIN produk_variasi pv ON pi.id = pv.id_produk_induk
    WHERE pi.nama_produk LIKE '%Asam Urat%' OR pi.nama_produk LIKE '%Kolesterol%' OR pi.nama_produk LIKE '%Tensimeter%'
");
echo "Master Data Produk:\n";
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['id']} | Produk: {$r['nama_produk']} | Kategori: '{$r['kategori']}' | Var: {$r['nama_variasi']}\n";
}

$stmt2 = $pdo->query("
    SELECT dob.id, dob.jenis_komoditas, pi.nama_produk, pi.kategori, dob.id_batch
    FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE dob.id_sesi = 28 AND (pi.nama_produk LIKE '%Asam Urat%' OR pi.nama_produk LIKE '%Kolesterol%' OR pi.nama_produk LIKE '%Tensimeter%')
");
echo "\nDetail Opname Batch (Sesi 28):\n";
while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo "ID Opname: {$r['id']} | Produk: {$r['nama_produk']} | dob.jenis_komoditas: '{$r['jenis_komoditas']}' | pi.kategori: '{$r['kategori']}' | id_batch: " . ($r['id_batch'] ?? 'NULL') . "\n";
}

