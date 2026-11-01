<?php
require 'config/koneksi.php';

echo "=== PENERIMAAN DENGAN RETUR ===\n";
$returs = $pdo->query("
    SELECT pd.id, pd.id_penerimaan, ps.no_referensi, pd.qty_pesan, pd.qty_terima, pd.qty_rusak, pd.status_retur, pd.catatan_retur, pi.nama_produk
    FROM penerimaan_detail pd
    JOIN penerimaan_stok ps ON pd.id_penerimaan = ps.id
    JOIN produk_variasi pv ON pd.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pd.qty_rusak > 0
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($returs as $r) {
    echo "Ref: {$r['no_referensi']} | Produk: {$r['nama_produk']} | Pesan: {$r['qty_pesan']} | Terima: {$r['qty_terima']} | Rusak: {$r['qty_rusak']} | Status: {$r['status_retur']}\n";
}

echo "\n=== KARTU STOK ALASAN MUTASI DISTINCT ===\n";
$alasan = $pdo->query("
    SELECT alasan_mutasi, jenis_mutasi, COUNT(*) as cnt
    FROM kartu_stok
    GROUP BY alasan_mutasi, jenis_mutasi
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($alasan as $a) {
    echo "Alasan: {$a['alasan_mutasi']} | Jenis: {$a['jenis_mutasi']} | Count: {$a['cnt']}\n";
}

