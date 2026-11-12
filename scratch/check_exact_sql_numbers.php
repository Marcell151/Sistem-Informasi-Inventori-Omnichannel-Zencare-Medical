<?php
require_once __DIR__ . '/../config/koneksi.php';

function q($pdo, $sql) {
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

echo "=== CEK DATA STOK BASELINE ===\n";

// 1. Cefadroxil
echo "\n--- Cefadroxil ---\n";
print_r(q($pdo, "SELECT pi.nama_produk, pv.id as var_id, pv.nama_variasi, pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, sb.id as batch_id, sb.no_batch, sb.stok_sisa, sb.tgl_exp FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id JOIN stok_batch sb ON sb.id_variasi = pv.id WHERE pi.nama_produk LIKE '%Cefadroxil%'"));

// 2. Sanmol
echo "\n--- Sanmol ---\n";
print_r(q($pdo, "SELECT pi.nama_produk, pv.id as var_id, pv.nama_variasi, pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, sb.id as batch_id, sb.no_batch, sb.stok_sisa, sb.tgl_exp FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id JOIN stok_batch sb ON sb.id_variasi = pv.id WHERE pi.nama_produk LIKE '%Sanmol%'"));

// 3. Amoxicillin
echo "\n--- Amoxicillin ---\n";
print_r(q($pdo, "SELECT pi.nama_produk, pv.id as var_id, pv.nama_variasi, pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, sb.id as batch_id, sb.no_batch, sb.stok_sisa, sb.tgl_exp FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id JOIN stok_batch sb ON sb.id_variasi = pv.id WHERE pi.nama_produk LIKE '%Amoxicillin%'"));

// 4. Tensimeter
echo "\n--- Tensimeter ---\n";
print_r(q($pdo, "SELECT pi.nama_produk, pv.id as var_id, pv.nama_variasi, (SELECT COUNT(*) FROM unit_serial us WHERE us.id_variasi = pv.id AND us.status = 'Tersedia') as sn_count FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE pi.nama_produk LIKE '%Tensimeter%'"));

// 5. Termometer
echo "\n--- Termometer ---\n";
print_r(q($pdo, "SELECT pi.nama_produk, pv.id as var_id, pv.nama_variasi, (SELECT COUNT(*) FROM unit_serial us WHERE us.id_variasi = pv.id AND us.status = 'Tersedia') as sn_count FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE pi.nama_produk LIKE '%Termometer%'"));

// 6. Betadine
echo "\n--- Betadine ---\n";
print_r(q($pdo, "SELECT pi.nama_produk, pv.id as var_id, pv.nama_variasi, pv.satuan_kecil, sb.id as batch_id, sb.no_batch, sb.stok_sisa FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id JOIN stok_batch sb ON sb.id_variasi = pv.id WHERE pi.nama_produk LIKE '%Betadine%'"));

