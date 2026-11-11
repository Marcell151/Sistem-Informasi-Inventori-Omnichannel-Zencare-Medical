<?php
require __DIR__ . '/../config/db.php';
$pdo = DB::connect();

// Test fetching prices for products
$stmt = $pdo->query("
    SELECT pv.id AS id_variasi, pi.nama_produk, pv.nama_variasi, pv.harga_jual_kecil,
           COALESCE((
               SELECT pd.harga_beli 
               FROM penerimaan_detail pd 
               WHERE pd.id_variasi = pv.id 
               ORDER BY pd.id DESC LIMIT 1
           ), pv.harga_jual_kecil * 0.8) AS estimasi_harga_beli
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LIMIT 5
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['nama_produk']} | Jual: Rp " . number_format($row['harga_jual_kecil']) . " | Estimasi Beli: Rp " . number_format($row['estimasi_harga_beli']) . "\n";
}

