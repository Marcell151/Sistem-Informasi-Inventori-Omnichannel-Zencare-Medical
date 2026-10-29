<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

$stmt = $pdo->query("
    SELECT pv.id, pv.sku_variasi, pi.nama_produk, pv.nama_variasi, pv.rasio_konversi, pi.kategori,
           COALESCE(st.stok, 0) as stok_toko,
           COALESCE(SUM(sb.stok_sisa), 0) as sum_batch_sisa,
           COUNT(sb.id) as count_batch,
           COUNT(us.id) as count_sn
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_toko st ON st.id_variasi = pv.id
    LEFT JOIN stok_batch sb ON sb.id_variasi = pv.id
    LEFT JOIN unit_serial us ON us.id_variasi = pv.id AND us.status = 'Tersedia'
    GROUP BY pv.id
    ORDER BY pv.id ASC
");
$rows = $stmt->fetchAll();

foreach ($rows as $r) {
    echo sprintf(
        "ID %-3d | %-35s | Rasio %-2d | Toko: %-5d | BatchSum: %-5d | Batches: %-2d | SNs: %-2d\n",
        $r['id'],
        substr($r['nama_produk'] . ' ' . $r['nama_variasi'], 0, 35),
        $r['rasio_konversi'],
        $r['stok_toko'],
        $r['sum_batch_sisa'],
        $r['count_batch'],
        $r['count_sn']
    );
}

