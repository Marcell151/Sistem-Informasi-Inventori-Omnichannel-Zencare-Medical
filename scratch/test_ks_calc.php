<?php
require_once __DIR__ . '/../config/koneksi.php';

function testKartuStokRender($pdo, $id_variasi) {
    // Exact logic from laporan/kartu_stok.php
    $stmtProd = $pdo->prepare("
        SELECT pv.sku_variasi, pi.nama_produk, pv.nama_variasi, pi.kategori,
               pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, pv.stok_minimum_kecil, pv.stok_minimum_besar, sc.stok
        FROM produk_variasi pv
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        LEFT JOIN stok_toko sc ON pv.id = sc.id_variasi 
        WHERE pv.id = ?
    ");
    $stmtProd->execute([$id_variasi]);
    $produkData = $stmtProd->fetch();
    $rasio = max(1, intval($produkData['rasio_konversi']));

    if ($produkData['kategori'] === 'Alat Kesehatan') {
        $produkData['stok_riil_besar'] = floor(intval($produkData['stok']) / $rasio);
        $produkData['stok_riil_kecil'] = intval($produkData['stok']);
    } else {
        $stokDus = $pdo->prepare("SELECT COALESCE(SUM(stok_sisa),0) FROM stok_batch WHERE id_variasi=? AND no_batch NOT LIKE '%.%'");
        $stokDus->execute([$id_variasi]);
        $produkData['stok_riil_besar'] = floor($stokDus->fetchColumn() / $rasio);
        $produkData['stok_riil_kecil'] = intval($produkData['stok']);
    }

    echo "--- {$produkData['nama_produk']} (ID: $id_variasi) ---\n";
    echo "Kategori: {$produkData['kategori']}\n";
    echo "stok_riil_besar: {$produkData['stok_riil_besar']} {$produkData['satuan_besar']}\n";
    echo "stok_riil_kecil: {$produkData['stok_riil_kecil']} {$produkData['satuan_kecil']}\n";
}

testKartuStokRender($pdo, 1);
testKartuStokRender($pdo, 10);
testKartuStokRender($pdo, 5);
testKartuStokRender($pdo, 4);

