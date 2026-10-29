<?php
require 'config/koneksi.php';

echo "=== DISTINCT status_retur in penerimaan_detail ===\n";
print_r($pdo->query("SELECT status_retur, COUNT(*) as jml FROM penerimaan_detail GROUP BY status_retur")->fetchAll(PDO::FETCH_ASSOC));

echo "=== DETAILS of retur rows ===\n";
print_r($pdo->query("SELECT pd.id, pd.status_retur, pd.qty_rusak, pd.qty_pengganti_diterima, ps.no_referensi, s.nama_supplier 
FROM penerimaan_detail pd 
JOIN penerimaan_stok ps ON pd.id_penerimaan = ps.id 
LEFT JOIN supplier s ON ps.id_supplier = s.id 
WHERE pd.status_retur != 'tidak_ada'")->fetchAll(PDO::FETCH_ASSOC));
