<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

$s = $pdo->query("SELECT pba.*, p.no_invoice, p.created_at, sb.no_batch 
FROM pesanan_batch_alokasi pba 
JOIN penjualan p ON pba.id_penjualan = p.id 
JOIN stok_batch sb ON pba.id_batch = sb.id 
WHERE sb.no_batch LIKE 'HUFPIL%' 
ORDER BY pba.id DESC LIMIT 5");
print_r($s->fetchAll(PDO::FETCH_ASSOC));

