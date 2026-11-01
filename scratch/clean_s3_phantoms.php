<?php
require_once __DIR__ . '/../config/koneksi.php';

// Hapus baris Alkes pada sesi 3 yang sebenarnya tidak memiliki unit_serial
$del = $pdo->exec("
    DELETE dob FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    WHERE dob.id_sesi = 3 
      AND dob.jenis_komoditas = 'Alkes'
      AND NOT EXISTS (SELECT 1 FROM unit_serial us WHERE us.id_variasi = pv.id)
");

echo "Deleted $del phantom rows from session 3.\n";

$remaining = $pdo->query("SELECT jenis_komoditas, COUNT(*) as cnt FROM detail_opname_batch WHERE id_sesi = 3 GROUP BY jenis_komoditas")->fetchAll(PDO::FETCH_ASSOC);
print_r($remaining);

