<?php
require_once __DIR__ . '/../config/koneksi.php';

echo "=== 1. CEK ARTIFAK DATA UJI DALAM DATABASE ===\n";
$cntBatch = $pdo->query("SELECT COUNT(*) FROM stok_batch WHERE no_batch LIKE '%TEST%'")->fetchColumn();
$cntSn    = $pdo->query("SELECT COUNT(*) FROM unit_serial WHERE serial_number LIKE '%TEST%'")->fetchColumn();
$cntTrx   = $pdo->query("SELECT COUNT(*) FROM penjualan WHERE no_invoice LIKE '%TEST%'")->fetchColumn();
$cntPba   = $pdo->query("SELECT COUNT(*) FROM pesanan_batch_alokasi WHERE id_penjualan NOT IN (SELECT id FROM penjualan)")->fetchColumn();

echo "stok_batch dg '%TEST%'           : $cntBatch\n";
echo "unit_serial dg '%TEST%'          : $cntSn\n";
echo "penjualan dg '%TEST%'            : $cntTrx\n";
echo "pesanan_batch_alokasi yatim      : $cntPba\n\n";

echo "=== 2. ANGKA DASHBOARD SAAT INI (index.php) ===\n";
$stokObatBesar = (int)$pdo->query("
    SELECT COALESCE(SUM(FLOOR(sb.stok_sisa / pv.rasio_konversi)), 0)
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pi.kategori != 'Alat Kesehatan' AND sb.no_batch NOT LIKE '%.%' AND sb.stok_sisa > 0
")->fetchColumn();

$stokAlkesUnit = (int)$pdo->query("
    SELECT COALESCE(SUM(st.stok), 0)
    FROM stok_toko st
    JOIN produk_variasi pv ON st.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pi.kategori = 'Alat Kesehatan' AND st.stok > 0
")->fetchColumn();

$stokEceranObat = (int)$pdo->query("
    SELECT COALESCE(SUM(sb.stok_sisa), 0)
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pi.kategori != 'Alat Kesehatan' AND sb.no_batch LIKE '%.%' AND sb.stok_sisa > 0
")->fetchColumn();

$totalStokFisik = (int)$pdo->query("SELECT COALESCE(SUM(stok),0) FROM stok_toko")->fetchColumn();

echo "Stok Obat Besar (Dus/Box) : $stokObatBesar Box\n";
echo "Stok Eceran Terbuka Obat  : $stokEceranObat Pcs/Strip\n";
echo "Stok Alkes (Unit Utuh)    : $stokAlkesUnit Unit\n";
echo "Total Stok Fisik Toko     : $totalStokFisik Unit/Pcs\n";

