<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$q = $pdo->query("
    SELECT pi.id, pi.nama_produk, pi.kategori, COUNT(pv.id) AS jml_variasi
    FROM produk_induk pi
    JOIN produk_variasi pv ON pv.id_produk_induk = pi.id
    WHERE pi.is_active = 1 AND pv.is_active = 1
    GROUP BY pi.id, pi.nama_produk, pi.kategori
    HAVING COUNT(pv.id) > 1
    ORDER BY jml_variasi DESC
")->fetchAll(PDO::FETCH_ASSOC);

echo "Daftar Produk Induk yang Memiliki Lebih dari 1 Variasi:\n";
foreach ($q as $row) {
    echo "- {$row['nama_produk']} ({$row['kategori']}): {$row['jml_variasi']} Variasi\n";
    $vars = $pdo->query("SELECT nama_variasi, sku_variasi FROM produk_variasi WHERE id_produk_induk = {$row['id']} AND is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($vars as $v) {
        echo "  * {$v['nama_variasi']} [SKU: {$v['sku_variasi']}]\n";
    }
}

