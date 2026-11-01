<?php
require __DIR__ . '/../config/koneksi.php';

echo "=== COLUMNS PENERIMAAN_STOK ===\n";
print_r($pdo->query("DESCRIBE penerimaan_stok")->fetchAll(PDO::FETCH_COLUMN));

echo "=== 1. CEK DATA PENERIMAAN DENGAN STATUS RETUR ===\n";
$stmt = $pdo->query("SELECT pd.id, pd.id_penerimaan, ps.no_referensi, pd.id_variasi, pv.nama_variasi, pi.nama_produk, pi.kategori, pd.qty_terima, pd.qty_rusak, pd.status_retur, pd.catatan_retur 
                     FROM penerimaan_detail pd
                     JOIN penerimaan_stok ps ON pd.id_penerimaan = ps.id
                     JOIN produk_variasi pv ON pd.id_variasi = pv.id
                     JOIN produk_induk pi ON pv.id_produk_induk = pi.id
                     WHERE pd.status_retur != 'tidak_ada' OR pd.qty_rusak > 0");
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Penerimaan: {$r['no_referensi']} | {$r['nama_produk']} - {$r['nama_variasi']} ({$r['kategori']}) | Qty Terima: {$r['qty_terima']} | Rusak: {$r['qty_rusak']} | Status Retur: {$r['status_retur']}\n";
}

echo "\n=== 2. CEK UNIT_SERIAL DENGAN STATUS RETUR/RUSAK ===\n";
$stmt2 = $pdo->query("SELECT us.id, us.id_variasi, pv.nama_variasi, pi.nama_produk, us.serial_number, us.status, us.catatan 
                      FROM unit_serial us
                      JOIN produk_variasi pv ON us.id_variasi = pv.id
                      JOIN produk_induk pi ON pv.id_produk_induk = pi.id
                      WHERE us.status = 'Retur/Rusak'");
while($r2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r2['id']} | {$r2['nama_produk']} | SN: {$r2['serial_number']} | Status: {$r2['status']} | Catatan: {$r2['catatan']}\n";
}
