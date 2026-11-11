<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();
$stmt = $pdo->query('SELECT id, kode_sesi, status, tahap FROM sesi_opname ORDER BY id DESC LIMIT 1');
$s = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Session: " . json_encode($s) . PHP_EOL;

$stmt2 = $pdo->query('SELECT COUNT(*) as cnt, COUNT(id_batch) as with_batch, SUM(CASE WHEN id_batch IS NULL THEN 1 ELSE 0 END) as sn_alkes FROM detail_opname_batch WHERE id_sesi = ' . $s['id']);
echo "Counts: " . json_encode($stmt2->fetch(PDO::FETCH_ASSOC)) . PHP_EOL;

$stmt3 = $pdo->query("SELECT dob.id, pi.nama_produk, dob.id_batch, dob.satuan_tipe, dob.hitung_fisik, dob.selisih, dob.status_mutasi FROM detail_opname_batch dob JOIN produk_variasi pv ON dob.id_variasi = pv.id JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE dob.id_sesi = {$s['id']} AND (pi.nama_produk LIKE '%Asam Urat%' OR pi.nama_produk LIKE '%Kolesterol%' OR pi.nama_produk LIKE '%Alkohol%')");
echo "Items Asam Urat/Kolesterol/Alkohol:\n";
while ($r = $stmt3->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['id']} | {$r['nama_produk']} | Batch: " . ($r['id_batch'] ?? 'NULL') . " | Sat: {$r['satuan_tipe']} | Hitung: {$r['hitung_fisik']} | Selisih: {$r['selisih']} | Mutasi: {$r['status_mutasi']}\n";
}
