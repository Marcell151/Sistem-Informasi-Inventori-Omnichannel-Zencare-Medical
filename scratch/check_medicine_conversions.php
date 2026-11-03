<?php
require __DIR__ . '/../config/koneksi.php';

$obatVariasi = $pdo->query("
    SELECT pv.id, pv.nama_variasi, pi.nama_produk, pv.rasio_konversi, pv.satuan_besar, pv.satuan_kecil,
           (SELECT COUNT(*) FROM stok_batch sb WHERE sb.id_variasi = pv.id AND sb.no_batch LIKE '%.%') as sub_batch_count,
           (SELECT SUM(sb.stok_sisa) FROM stok_batch sb WHERE sb.id_variasi = pv.id AND sb.no_batch LIKE '%.%') as sub_batch_stok
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pi.kategori = 'Obat'
    ORDER BY pv.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

echo "=== DAFTAR OBAT & STATUS SUB-BATCH KONVERSI ===\n";
$sudah = 0; $belum = 0;
foreach ($obatVariasi as $o) {
    $hasSub = $o['sub_batch_count'] > 0;
    if ($hasSub) $sudah++; else $belum++;
    echo sprintf(
        "ID: %-2d | %-25s | Rasio: 1 %-5s = %2d %-6s | Sub-Batch: %d (Sisa: %4d) | %s\n",
        $o['id'],
        substr($o['nama_produk'] . ' ' . $o['nama_variasi'], 0, 25),
        $o['satuan_besar'],
        $o['rasio_konversi'],
        $o['satuan_kecil'],
        $o['sub_batch_count'],
        intval($o['sub_batch_stok']),
        $hasSub ? "SUDAH ADA" : "BELUM ADA"
    );
}
echo "Total Obat: " . count($obatVariasi) . " | Sudah Ada Sub-Batch: $sudah | Belum Ada: $belum\n";

