<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

$stmt = $pdo->query("
    SELECT laf.id, laf.id_penjualan, laf.id_variasi, laf.batch_seharusnya, laf.status,
           pba.id_penjualan AS matched_penjualan, p.no_invoice, pba.created_at AS matched_time
    FROM log_anomali_fefo laf
    JOIN stok_batch sb ON (sb.id_variasi = laf.id_variasi AND sb.no_batch = laf.batch_seharusnya)
    JOIN pesanan_batch_alokasi pba ON pba.id_batch = sb.id AND pba.id_penjualan > laf.id_penjualan
    JOIN penjualan p ON pba.id_penjualan = p.id
    WHERE laf.status = 'terlewat'
");
$matches = $stmt->fetchAll();
print_r($matches);

