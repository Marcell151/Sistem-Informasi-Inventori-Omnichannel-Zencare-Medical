<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("
    SELECT dob.id, pi.nama_produk, pv.nama_variasi, dob.satuan_tipe, dob.stok_sistem_fisik,
           dob.hitung_fisik, dob.fisik_baik, dob.fisik_rusak, dob.fisik_expired, dob.selisih, dob.status_mutasi, dob.status_hitung
    FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE dob.id_sesi = (SELECT id FROM sesi_opname WHERE status = 'Aktif' ORDER BY id DESC LIMIT 1)
      AND dob.status_hitung = 'Sudah'
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Count of sudah hitung: " . count($rows) . PHP_EOL;
foreach ($rows as $r) {
    echo "ID: {$r['id']} | {$r['nama_produk']} ({$r['satuan_tipe']}) | Sys: {$r['stok_sistem_fisik']} | Baik: " . var_export($r['fisik_baik'], true) . " | Rusak: " . var_export($r['fisik_rusak'], true) . " | Exp: " . var_export($r['fisik_expired'], true) . " | Hitung: " . var_export($r['hitung_fisik'], true) . " | Selisih: {$r['selisih']} | Mut: {$r['status_mutasi']}" . PHP_EOL;
}

