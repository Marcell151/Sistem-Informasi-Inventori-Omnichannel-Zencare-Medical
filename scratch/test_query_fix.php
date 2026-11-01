<?php
require_once __DIR__ . '/../config/koneksi.php';

$obatBMHP = $pdo->query("
    SELECT sb.id AS id_batch, pi.nama_produk, pv.nama_variasi, pi.kategori
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pv.is_active = 1
")->fetchAll(PDO::FETCH_ASSOC);

$alkesSN = $pdo->query("
    SELECT pv.id AS id_variasi, pi.nama_produk, pv.nama_variasi,
           (SELECT COUNT(*) FROM unit_serial us WHERE us.id_variasi = pv.id AND us.status = 'Tersedia') as sn_tersedia,
           (SELECT COUNT(*) FROM unit_serial us WHERE us.id_variasi = pv.id) as sn_total
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pv.is_active = 1 AND EXISTS (SELECT 1 FROM unit_serial us WHERE us.id_variasi = pv.id)
")->fetchAll(PDO::FETCH_ASSOC);

echo "Total Batch Obat & BMHP: " . count($obatBMHP) . "\n";
echo "Total Alkes Ber-SN: " . count($alkesSN) . "\n";
foreach ($alkesSN as $a) {
    echo "  - {$a['nama_produk']} ({$a['nama_variasi']}) -> Tersedia: {$a['sn_tersedia']}, Total: {$a['sn_total']}\n";
}

