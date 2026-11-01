<?php
require_once __DIR__ . '/../config/koneksi.php';
$s = $pdo->query("SELECT * FROM sesi_opname WHERE status = 'Aktif'")->fetch(PDO::FETCH_ASSOC);
print_r($s);
if ($s) {
    $c = $pdo->query("SELECT jenis_komoditas, COUNT(*) as cnt FROM detail_opname_batch WHERE id_sesi = {$s['id']} GROUP BY jenis_komoditas")->fetchAll(PDO::FETCH_ASSOC);
    print_r($c);
}

