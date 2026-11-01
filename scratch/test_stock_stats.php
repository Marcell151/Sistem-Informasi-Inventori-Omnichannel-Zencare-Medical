<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$induk = (int)$pdo->query('SELECT COUNT(*) FROM produk_induk WHERE is_active=1')->fetchColumn();
$variasi = (int)$pdo->query('SELECT COUNT(*) FROM produk_variasi WHERE is_active=1')->fetchColumn();
echo "Induk: $induk, Variasi: $variasi\n";

$stokBesar = (int)$pdo->query('
    SELECT COALESCE(SUM(FLOOR(sb.stok_sisa / pv.rasio_konversi)), 0) 
    FROM stok_batch sb 
    JOIN produk_variasi pv ON sb.id_variasi = pv.id 
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id 
    WHERE pi.kategori != "Alat Kesehatan" AND sb.no_batch NOT LIKE "%.%" AND sb.stok_sisa > 0
')->fetchColumn();

$stokKecilObat = (int)$pdo->query('
    SELECT COALESCE(SUM(sb.stok_sisa), 0) 
    FROM stok_batch sb 
    JOIN produk_variasi pv ON sb.id_variasi = pv.id 
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id 
    WHERE pi.kategori != "Alat Kesehatan" AND sb.no_batch LIKE "%.%" AND sb.stok_sisa > 0
')->fetchColumn();

$stokAlkes = (int)$pdo->query('
    SELECT COALESCE(SUM(st.stok), 0) 
    FROM stok_toko st 
    JOIN produk_variasi pv ON st.id_variasi = pv.id 
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id 
    WHERE pi.kategori = "Alat Kesehatan" AND st.stok > 0
')->fetchColumn();

$totalStokFisik = (int)$pdo->query('SELECT COALESCE(SUM(stok), 0) FROM stok_toko')->fetchColumn();

echo "Stok Satuan Besar (Dus/Box): $stokBesar\n";
echo "Stok Satuan Kecil Obat (Tablet/Kapsul/Pcs): $stokKecilObat\n";
echo "Stok Alkes (Unit SN): $stokAlkes\n";
echo "Total Stok Fisik Satuan Kecil (stok_toko): $totalStokFisik\n";

