<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->prepare("
    UPDATE kartu_stok ks
    JOIN produk_variasi pv ON ks.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    SET ks.satuan_tipe = 'besar'
    WHERE pi.kategori = 'Alat Kesehatan' AND ks.satuan_tipe = 'kecil'
");
$stmt->execute();
$affected = $stmt->rowCount();
echo "Migrated $affected kartu_stok records of Alkes to 'besar'.\n";

// Re-check
$res = $pdo->query("
    SELECT ks.satuan_tipe, COUNT(*) as cnt
    FROM kartu_stok ks
    JOIN produk_variasi pv ON ks.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pi.kategori = 'Alat Kesehatan'
    GROUP BY ks.satuan_tipe
")->fetchAll(PDO::FETCH_ASSOC);
print_r($res);

