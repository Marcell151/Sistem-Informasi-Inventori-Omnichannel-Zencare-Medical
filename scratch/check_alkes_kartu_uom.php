<?php
require_once __DIR__ . '/../config/koneksi.php';

$res = $pdo->query("
    SELECT ks.satuan_tipe, COUNT(*) as total_records
    FROM kartu_stok ks
    JOIN produk_variasi pv ON ks.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pi.kategori = 'Alat Kesehatan'
    GROUP BY ks.satuan_tipe
")->fetchAll(PDO::FETCH_ASSOC);
echo "Kartu Stok Alkes by satuan_tipe:\n";
print_r($res);

