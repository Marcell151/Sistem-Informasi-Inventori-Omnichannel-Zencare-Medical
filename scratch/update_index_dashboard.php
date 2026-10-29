<?php
$content = file_get_contents('index.php');

// 1. Tambah Query Anomali FEFO & Status Retur
$search1 = "// ─── DATA SUPERADMIN (Semua berbasis Kuantitas/Unit — tanpa Rupiah) ────────";
$insert1 = <<<'EOD'
// ─── ANOMALI FEFO & MONITORING RETUR (Bimbingan Dosen Poin 2) ─────────────
$anomaliHariIniCount = (int)$pdo->query("SELECT COUNT(*) FROM log_anomali_fefo WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$listAnomaliHariIni = $pdo->query("
    SELECT a.id, a.id_penjualan, a.batch_diambil, a.batch_seharusnya, a.qty, a.created_at,
           COALESCE(p.no_invoice, CONCAT('TRX-', a.id_penjualan)) AS no_invoice,
           COALESCE(p.tipe_transaksi, 'pos') AS tipe_transaksi,
           CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama_produk,
           COALESCE(u.nama_lengkap, 'Kasir') AS operator
    FROM log_anomali_fefo a
    LEFT JOIN penjualan p ON a.id_penjualan = p.id
    LEFT JOIN produk_variasi pv ON a.id_variasi = pv.id
    LEFT JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN users u ON a.id_user = u.id
    WHERE DATE(a.created_at) = CURDATE()
    ORDER BY a.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$returMenungguCount = (int)$pdo->query("SELECT COUNT(*) FROM penerimaan_detail WHERE status_retur = 'menunggu_pengganti'")->fetchColumn();
$returSelesaiCount  = (int)$pdo->query("SELECT COUNT(*) FROM penerimaan_detail WHERE status_retur = 'selesai_tukar_barang'")->fetchColumn();
$listReturMonitoring = $pdo->query("
    SELECT pd.id, pd.status_retur, pd.qty_rusak, pd.qty_pengganti_diterima, pd.no_batch, pd.no_batch_pengganti, pd.tgl_selesai_retur, pd.catatan_retur,
           ps.no_referensi, ps.tanggal_terima,
           COALESCE(s.nama, 'Supplier') AS nama_supplier,
           CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama_produk,
           pv.satuan_besar, pv.satuan_kecil
    FROM penerimaan_detail pd
    JOIN penerimaan_stok ps ON pd.id_penerimaan = ps.id
    LEFT JOIN supplier s ON ps.id_supplier = s.id
    JOIN produk_variasi pv ON pd.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pd.status_retur IN ('menunggu_pengganti', 'selesai_tukar_barang')
    ORDER BY (CASE WHEN pd.status_retur = 'menunggu_pengganti' THEN 1 ELSE 2 END), pd.id DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// ─── DATA SUPERADMIN (Semua berbasis Kuantitas/Unit — tanpa Rupiah) ────────
EOD;

if (strpos($content, '$anomaliHariIniCount') === false) {
    if (strpos($content, $search1) !== false) {
        $content = str_replace($search1, $insert1, $content);
        echo "Query injected successfully.\n";
    } else {
        echo "Error: search1 not found\n";
    }
} else {
    echo "Queries already present.\n";
}

file_put_contents('index.php', $content);
