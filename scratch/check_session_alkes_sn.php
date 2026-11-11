<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

$stmt = $pdo->query("
    SELECT dob.id, dob.id_variasi, dob.id_batch, dob.jenis_komoditas, pi.nama_produk,
           (SELECT COUNT(*) FROM unit_serial WHERE id_variasi = dob.id_variasi) AS sn_count,
           (SELECT COUNT(*) FROM stok_batch WHERE id_variasi = dob.id_variasi) AS batch_count
    FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE dob.id_sesi = 28 AND dob.id_batch IS NULL
");
while ($r = $stmt->fetch()) {
    echo "ID: {$r['id']} | Var: {$r['id_variasi']} | {$r['nama_produk']} | SN: {$r['sn_count']} | Batch: {$r['batch_count']}" . PHP_EOL;
}

