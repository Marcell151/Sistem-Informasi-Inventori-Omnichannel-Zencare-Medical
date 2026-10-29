<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

$stmt = $pdo->prepare("SELECT v.id, v.sku_variasi, CONCAT(i.nama_produk,' - ',v.nama_variasi) AS nama, v.rasio_konversi, sc.stok as stok_toko FROM produk_variasi v JOIN produk_induk i ON v.id_produk_induk=i.id LEFT JOIN stok_toko sc ON sc.id_variasi=v.id WHERE v.id IN (1, 6, 10, 13, 24, 26)");
$stmt->execute();
$items = $stmt->fetchAll();

foreach ($items as $item) {
    echo "ID: {$item['id']} | {$item['nama']} | Rasio: {$item['rasio_konversi']} | StokToko: {$item['stok_toko']}\n";
    $bStmt = $pdo->prepare("SELECT * FROM stok_batch WHERE id_variasi = ?");
    $bStmt->execute([$item['id']]);
    $batches = $bStmt->fetchAll();
    $sum = 0;
    foreach ($batches as $b) {
        echo "   Batch: {$b['no_batch']} | StokSisa: {$b['stok_sisa']} | Exp: {$b['tgl_exp']}\n";
        $sum += $b['stok_sisa'];
    }
    echo "   Total Sum Batch: $sum\n\n";
}

