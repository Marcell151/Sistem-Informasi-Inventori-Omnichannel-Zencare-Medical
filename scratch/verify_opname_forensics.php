<?php
require __DIR__ . '/../config/koneksi.php';

echo "=== 1. CEK STATUS UNIT_SERIAL DI DB ===\n";
$q = $pdo->query("SELECT status, COUNT(*) as cnt FROM unit_serial GROUP BY status");
while($r = $q->fetch(PDO::FETCH_ASSOC)) {
    echo "Status: {$r['status']} | Count: {$r['cnt']}\n";
}

echo "\n=== 2. CEK STRUKTUR ENUM UNIT_SERIAL.STATUS ===\n";
$q2 = $pdo->query("SHOW COLUMNS FROM unit_serial LIKE 'status'");
$r2 = $q2->fetch(PDO::FETCH_ASSOC);
echo "Type: {$r2['Type']}\n";

echo "\n=== 3. CEK KORELASI UNIT_SERIAL DENGAN PENJUALAN ===\n";
$q3 = $pdo->query("SELECT us.serial_number, us.id_variasi, us.status, us.id_penjualan, p.no_invoice, p.status_pesanan 
                   FROM unit_serial us 
                   LEFT JOIN penjualan p ON us.id_penjualan = p.id 
                   WHERE us.id_penjualan IS NOT NULL");
while($r3 = $q3->fetch(PDO::FETCH_ASSOC)) {
    echo "SN: {$r3['serial_number']} | Status: {$r3['status']} | Order: {$r3['no_invoice']} | Status Order: {$r3['status_pesanan']}\n";
}

