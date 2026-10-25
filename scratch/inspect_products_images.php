<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("
    SELECT pv.id, pi.id as id_induk, pi.nama_produk, pv.nama_variasi, pi.kategori, pv.sku_variasi, pv.gambar 
    FROM produk_variasi pv 
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id 
    ORDER BY pi.kategori ASC, pi.nama_produk ASC, pv.nama_variasi ASC
");
$rows = $stmt->fetchAll();
echo "Total Variasi: " . count($rows) . "\n\n";
foreach ($rows as $r) {
    echo "ID: {$r['id']} | [{$r['kategori']}] {$r['nama_produk']} - {$r['nama_variasi']} ({$r['sku_variasi']})\n";
    echo "   Gambar: " . ($r['gambar'] ?: '(KOSONG)') . "\n";
}
