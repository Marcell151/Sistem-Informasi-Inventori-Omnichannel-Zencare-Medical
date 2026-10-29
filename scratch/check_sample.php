<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("SELECT id, sku_variasi, nama_variasi, satuan_kecil, satuan_besar, rasio_konversi, stok_sisa_toko FROM (
    SELECT pv.id, pv.sku_variasi, pv.nama_variasi, pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, st.stok as stok_sisa_toko 
    FROM produk_variasi pv 
    LEFT JOIN stok_toko st ON st.id_variasi = pv.id
    WHERE pv.sku_variasi IN ('BET-15', 'AMX-500', 'PCT-500', 'ESY-GLU', 'OMR-TENS')
) t");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "=== PRODUK SAMPLE ===\n";
print_r($rows);

$stmt2 = $pdo->query("SELECT sb.*, pv.sku_variasi FROM stok_batch sb JOIN produk_variasi pv ON sb.id_variasi = pv.id WHERE pv.sku_variasi IN ('BET-15', 'AMX-500', 'PCT-500', 'ESY-GLU', 'OMR-TENS')");
$batches = $stmt2->fetchAll(PDO::FETCH_ASSOC);
echo "=== BATCHES SAMPLE ===\n";
print_r($batches);

