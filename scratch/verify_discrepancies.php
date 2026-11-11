<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

$stmt = $pdo->query("
    SELECT dob.id, pi.nama_produk, pv.nama_variasi, dob.jenis_komoditas, dob.satuan_tipe, 
           dob.stok_sistem_fisik, dob.hitung_fisik, dob.selisih, dob.fisik_rusak, dob.fisik_expired, dob.status_mutasi 
    FROM detail_opname_batch dob 
    JOIN produk_variasi pv ON dob.id_variasi = pv.id 
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id 
    WHERE dob.id_sesi = 28 AND (dob.selisih != 0 OR dob.fisik_rusak > 0 OR dob.fisik_expired > 0)
");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Total Discrepant Items in Session 28: " . count($items) . PHP_EOL;
foreach ($items as $it) {
    echo "ID: {$it['id']} | {$it['nama_produk']} ({$it['nama_variasi']}) | Sist: {$it['stok_sistem_fisik']} | Hit: {$it['hitung_fisik']} | Sel: {$it['selisih']} | Rusak: {$it['fisik_rusak']} | Exp: {$it['fisik_expired']} | Mutasi: {$it['status_mutasi']}\n";
}

