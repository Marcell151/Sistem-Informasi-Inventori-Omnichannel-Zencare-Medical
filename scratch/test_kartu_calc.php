<?php
require 'config/koneksi.php';
$_GET['id_variasi'] = 59;
$_GET['dari'] = '2026-10-01';
$_GET['sampai'] = '2026-10-25';

$id_variasi = 59;
$stmtProd = $pdo->prepare("
    SELECT pv.sku_variasi, pi.nama_produk, pv.nama_variasi, pi.kategori,
           pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, pv.stok_minimum_kecil, pv.stok_minimum_besar, sc.stok
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_toko sc ON pv.id = sc.id_variasi 
    WHERE pv.id = ?
");
$stmtProd->execute([$id_variasi]);
$produkData = $stmtProd->fetch();

$stokDus = $pdo->prepare("SELECT COALESCE(SUM(stok_sisa),0) FROM stok_batch WHERE id_variasi=? AND no_batch NOT LIKE '%.%'");
$stokDus->execute([$id_variasi]);
$produkData['stok_riil_besar'] = floor(intval($stokDus->fetchColumn()) / max(1, intval($produkData['rasio_konversi'])));

$stokEceran = $pdo->prepare("SELECT COALESCE(SUM(stok_sisa),0) FROM stok_batch WHERE id_variasi=? AND no_batch LIKE '%.%'");
$stokEceran->execute([$id_variasi]);
$produkData['stok_riil_kecil'] = intval($stokEceran->fetchColumn());

echo "STOK RIIL BESAR: " . $produkData['stok_riil_besar'] . "\n";
echo "STOK RIIL KECIL: " . $produkData['stok_riil_kecil'] . "\n";
echo "RASIO: " . $produkData['rasio_konversi'] . "\n\n";

$stmtLog = $pdo->prepare("
    SELECT ks.id, ks.tanggal, ks.keterangan as deskripsi, ks.jenis_mutasi, ks.qty, ks.no_ref_dokumen, ks.kanal, ks.alasan_mutasi,
           ks.satuan_tipe
    FROM kartu_stok ks
    WHERE ks.id_variasi = ? 
    ORDER BY ks.tanggal ASC, ks.id ASC
");
$stmtLog->execute([$id_variasi]);
$allLogs = $stmtLog->fetchAll(PDO::FETCH_ASSOC);

$allLogsBesar = [];
$allLogsKecil = [];
foreach ($allLogs as $log) {
    if ($log['satuan_tipe'] === 'besar') $allLogsBesar[] = $log;
    else $allLogsKecil[] = $log;
}

echo "=== LOG BESAR RAW ===\n";
print_r($allLogsBesar);

$allLogsBesar = array_reverse($allLogsBesar);
$currentBalanceBesar = intval($produkData['stok_riil_besar']);
$logDataBesar = [];

foreach ($allLogsBesar as $log) {
    $q = intval($log['qty']);
    
    $log['qty_calc'] = $q;
    $log['sisa_stok_calc'] = $currentBalanceBesar; 
    
    // Hitung mundur untuk transaksi sebelumnya
    if ($log['jenis_mutasi'] === 'Masuk' || $log['jenis_mutasi'] === 'Pengembalian/Batal') {
        $currentBalanceBesar -= $q; 
    } else if ($log['jenis_mutasi'] === 'Keluar' || $log['jenis_mutasi'] === 'Karantina') {
        $currentBalanceBesar += $q; 
    }
    $logDataBesar[] = $log;
}
$logDataBesar = array_reverse($logDataBesar);
echo "=== LOG BESAR PROCESSED (as in kartu_stok.php) ===\n";
foreach ($logDataBesar as $l) {
    echo "ID: {$l['id']} | Tgl: {$l['tanggal']} | {$l['jenis_mutasi']} | Qty DB: {$l['qty']} -> Qty Calc: {$l['qty_calc']} | Sisa Calc: {$l['sisa_stok_calc']} | {$l['deskripsi']}\n";
}

echo "\n=== LOG KECIL RAW ===\n";
$allLogsKecilRev = array_reverse($allLogsKecil);
$currentBalanceKecil = intval($produkData['stok_riil_kecil']);
$logDataKecil = [];

foreach ($allLogsKecilRev as $log) {
    $q = intval($log['qty']);
    $log['qty_calc'] = $q;
    $log['sisa_stok_calc'] = $currentBalanceKecil; 
    
    // Hitung mundur
    if ($log['jenis_mutasi'] === 'Masuk' || $log['jenis_mutasi'] === 'Pengembalian/Batal') {
        $currentBalanceKecil -= $q; 
    } else if ($log['jenis_mutasi'] === 'Keluar' || $log['jenis_mutasi'] === 'Karantina') {
        $currentBalanceKecil += $q; 
    }
    $logDataKecil[] = $log;
}
$logDataKecil = array_reverse($logDataKecil);
echo "=== LOG KECIL PROCESSED ===\n";
foreach ($logDataKecil as $l) {
    echo "ID: {$l['id']} | Tgl: {$l['tanggal']} | {$l['jenis_mutasi']} | Qty: {$l['qty_calc']} | Sisa: {$l['sisa_stok_calc']} | {$l['deskripsi']}\n";
}
