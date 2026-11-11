<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/opname_helper.php';

$s = cekSesiOpnameAktif($pdo);
$stmt = $pdo->prepare('SELECT COUNT(*) as total, 
    SUM(CASE WHEN status_hitung = "Sudah" THEN 1 ELSE 0 END) as sudah_hitung,
    SUM(CASE WHEN status_hitung = "Belum" OR status_hitung IS NULL THEN 1 ELSE 0 END) as belum_hitung,
    SUM(CASE WHEN status_mutasi = "Sesuai" THEN 1 ELSE 0 END) as sesuai,
    SUM(CASE WHEN status_mutasi = "Belum Dimutasi" THEN 1 ELSE 0 END) as berselisih,
    SUM(CASE WHEN status_mutasi = "Sudah Dimutasi" THEN 1 ELSE 0 END) as sudah_dimutasi
FROM detail_opname_batch WHERE id_sesi = ?');
$stmt->execute([$s['id']]);
print_r($stmt->fetch(PDO::FETCH_ASSOC));

