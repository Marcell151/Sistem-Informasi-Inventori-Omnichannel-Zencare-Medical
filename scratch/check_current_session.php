<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$res = $pdo->query("SELECT id, jenis_komoditas, status_hitung, fisik_baik, fisik_rusak, fisik_expired FROM detail_opname_batch WHERE id_sesi = 34")->fetchAll();
$sudah = 0;
$belum = 0;
foreach ($res as $r) {
    if ($r['status_hitung'] === 'Sudah') $sudah++;
    else $belum++;
}
echo "Session 34: Sudah = $sudah, Belum = $belum, Total = " . count($res) . "\n";
print_r(array_slice($res, 0, 15));
