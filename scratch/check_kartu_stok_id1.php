<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

$stmt = $pdo->prepare("SELECT * FROM kartu_stok WHERE id_variasi = 1 ORDER BY id ASC");
$stmt->execute();
$rows = $stmt->fetchAll();
foreach ($rows as $r) {
    echo "ID: {$r['id']} | Satuan: {$r['satuan_tipe']} | Mutasi: {$r['jenis_mutasi']} | Qty: {$r['qty']} | Sisa: {$r['sisa_stok']} | Alasan: {$r['alasan_mutasi']} | Ket: {$r['keterangan']}\n";
}

