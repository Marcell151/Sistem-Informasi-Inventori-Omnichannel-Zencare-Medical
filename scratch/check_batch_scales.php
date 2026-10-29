<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

$stmt = $pdo->query("
    SELECT sb.id, sb.id_variasi, sb.no_batch, sb.stok_sisa, pv.rasio_konversi, st.stok as stok_toko,
           (sb.stok_sisa * pv.rasio_konversi) as calculated_pcs
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    LEFT JOIN stok_toko st ON st.id_variasi = pv.id
    WHERE pv.rasio_konversi > 1 AND sb.no_batch NOT LIKE '%.%'
");
$batches = $stmt->fetchAll();

foreach ($batches as $b) {
    echo "Batch ID: {$b['id']} | VarID: {$b['id_variasi']} | Batch: {$b['no_batch']} | CurrentStokSisa: {$b['stok_sisa']} | Rasio: {$b['rasio_konversi']} | Calc: {$b['calculated_pcs']} | TokoStok: {$b['stok_toko']}\n";
}

