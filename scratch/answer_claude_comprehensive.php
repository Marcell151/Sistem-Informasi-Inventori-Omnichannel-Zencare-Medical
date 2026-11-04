<?php
require_once __DIR__ . '/../config/koneksi.php';

echo "=== DAFTAR TABEL DALAM DATABASE ===\n";
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);

echo "\n=== CEK STRUKTUR TABEL KARTU_STOK ===\n";
$ksCols = $pdo->query("SHOW COLUMNS FROM kartu_stok")->fetchAll(PDO::FETCH_ASSOC);
foreach ($ksCols as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}

echo "\n=== CEK NILAI ENUM / DISTINCT ALASAN_MUTASI DI KARTU_STOK ===\n";
$distinctAlasan = $pdo->query("SELECT DISTINCT alasan_mutasi FROM kartu_stok")->fetchAll(PDO::FETCH_COLUMN);
print_r($distinctAlasan);

echo "\n=== CEK STRUKTUR TABEL DETAIL_OPNAME_BATCH ===\n";
$dobCols = $pdo->query("SHOW COLUMNS FROM detail_opname_batch")->fetchAll(PDO::FETCH_ASSOC);
foreach ($dobCols as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}

echo "\n=== CEK TRANSAKSI ALKES DI DETAIL_PENJUALAN & KARTU_STOK ===\n";
$alkesTrx = $pdo->query("
    SELECT dp.id, dp.id_penjualan, dp.id_variasi, dp.satuan_tipe, dp.harga_satuan, dp.jumlah, pv.nama_variasi, pi.nama_produk, pi.jenis_produk
    FROM detail_penjualan dp
    JOIN produk_variasi pv ON dp.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pi.jenis_produk = 'Alat Kesehatan'
    ORDER BY dp.id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
print_r($alkesTrx);

echo "\n=== CEK APAKAH ADA ALKES DENGAN SATUAN_TIPE 'kecil' DI DETAIL_PENJUALAN / KARTU_STOK ===\n";
$oldKecil = $pdo->query("
    SELECT COUNT(*) as cnt
    FROM detail_penjualan dp
    JOIN produk_variasi pv ON dp.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pi.jenis_produk = 'Alat Kesehatan' AND dp.satuan_tipe = 'kecil'
")->fetch(PDO::FETCH_ASSOC);
echo "Jumlah detail_penjualan Alkes dengan satuan_tipe = 'kecil': " . $oldKecil['cnt'] . "\n";

$oldKecilKartu = $pdo->query("
    SELECT COUNT(*) as cnt
    FROM kartu_stok ks
    JOIN produk_variasi pv ON ks.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pi.jenis_produk = 'Alat Kesehatan' AND ks.satuan_tipe = 'kecil'
")->fetch(PDO::FETCH_ASSOC);
echo "Jumlah kartu_stok Alkes dengan satuan_tipe = 'kecil': " . $oldKecilKartu['cnt'] . "\n";
