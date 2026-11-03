<?php
require __DIR__ . '/../config/koneksi.php';

echo "=== DAFTAR PRODUK & VARIASI ===\n";
$stmt = $pdo->query("SELECT pi.id, pi.nama_produk, pi.kategori, pv.id as id_var, pv.nama_variasi, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi, pv.harga_jual_kecil, pv.harga_jual_besar FROM produk_induk pi JOIN produk_variasi pv ON pi.id = pv.id_produk_induk");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['id']} | [{$r['kategori']}] {$r['nama_produk']} ({$r['nama_variasi']}) | Satuan: Besar={$r['satuan_besar']} / Kecil={$r['satuan_kecil']} (Rasio: {$r['rasio_konversi']}) | Kecil: {$r['harga_jual_kecil']} | Besar: {$r['harga_jual_besar']}\n";
}

echo "\n=== DETAIL PENJUALAN SATUAN_TIPE ===\n";
$stmt3 = $pdo->query("SELECT dp.satuan_tipe, pi.kategori, COUNT(*) as c FROM detail_penjualan dp JOIN produk_variasi pv ON dp.id_variasi = pv.id JOIN produk_induk pi ON pv.id_produk_induk = pi.id GROUP BY dp.satuan_tipe, pi.kategori");
while ($r3 = $stmt3->fetch(PDO::FETCH_ASSOC)) {
    echo "kategori: {$r3['kategori']} | dp.satuan_tipe: {$r3['satuan_tipe']} => {$r3['c']} baris\n";
}

echo "\n=== KARTU STOK SATUAN_TIPE ===\n";
$stmt4 = $pdo->query("SELECT ks.satuan_tipe, pi.kategori, COUNT(*) as c FROM kartu_stok ks JOIN produk_variasi pv ON ks.id_variasi = pv.id JOIN produk_induk pi ON pv.id_produk_induk = pi.id GROUP BY ks.satuan_tipe, pi.kategori");
while ($r4 = $stmt4->fetch(PDO::FETCH_ASSOC)) {
    echo "kategori: {$r4['kategori']} | ks.satuan_tipe: {$r4['satuan_tipe']} => {$r4['c']} baris\n";
}

echo "\n=== DASHBOARD QUERY CHECK (admin/index.php / dashboard.php) ===\n";
$dashFiles = ['admin/dashboard.php', 'admin/index.php', 'index.php'];
foreach ($dashFiles as $df) {
    if (file_exists(__DIR__ . '/../' . $df)) {
        echo "Found dashboard file: $df\n";
    }
}
