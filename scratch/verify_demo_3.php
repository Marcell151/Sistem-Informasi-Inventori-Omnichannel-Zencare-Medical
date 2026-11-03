<?php
require_once __DIR__ . '/../config/koneksi.php';

echo "=== 1. VERIFIKASI BATCH DUMMY ===\n";
$dummyCount = $pdo->query("SELECT count(*) FROM stok_batch WHERE no_batch LIKE '%EXP%' OR no_batch LIKE '%OLD%'")->fetchColumn();
echo "Jumlah batch dummy tersisa: $dummyCount (Harus 0)\n";

echo "\n=== 2. VERIFIKASI EXPIRY DATE HARI H (6 NOV) & H+1 (7 NOV) ===\n";
$expBatches = $pdo->query("
    SELECT sb.no_batch, sb.tgl_exp, sb.stok_sisa, pi.nama_produk, pv.nama_variasi 
    FROM stok_batch sb 
    JOIN produk_variasi pv ON sb.id_variasi = pv.id 
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id 
    WHERE sb.tgl_exp IN ('2026-11-06', '2026-11-07', '2026-11-12', '2026-11-20')
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($expBatches as $eb) {
    echo "Batch: {$eb['no_batch']} | Exp: {$eb['tgl_exp']} | Stok: {$eb['stok_sisa']} | {$eb['nama_produk']} - {$eb['nama_variasi']}\n";
}

echo "\n=== 3. VERIFIKASI SUB-BATCH KONVERSI 41 OBAT ===\n";
$subBatches = $pdo->query("
    SELECT count(DISTINCT sb.id_variasi) AS variasi_ber_sub, count(*) AS total_sub_batches
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pi.kategori = 'Obat' AND sb.no_batch LIKE '%.%'
")->fetch(PDO::FETCH_ASSOC);
echo "Variasi obat memiliki sub-batch: {$subBatches['variasi_ber_sub']} dari 41 variasi obat (Harus 41!)\n";
echo "Total sub-batch eceran aktif: {$subBatches['total_sub_batches']}\n";

echo "\n=== 4. VERIFIKASI SERIAL NUMBER ALKES FORMAT RESMI ===\n";
$dummySn = $pdo->query("SELECT count(*) FROM unit_serial WHERE serial_number LIKE 'OMR-TENS%' OR serial_number LIKE 'OMR-TH%' OR serial_number LIKE 'IRTHER%'")->fetchColumn();
echo "Jumlah SN dummy tersisa: $dummySn (Harus 0)\n";
$sampleSn = $pdo->query("SELECT serial_number, status FROM unit_serial LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
foreach ($sampleSn as $sn) {
    echo "Sample SN: {$sn['serial_number']} [{$sn['status']}]\n";
}

echo "\n=== 5. VERIFIKASI SKENARIO PEMBATALAN PESANAN ===\n";
$cancels = $pdo->query("
    SELECT no_invoice, status_pesanan, alasan_batal, dibatalkan_oleh, request_batal, request_batal_alasan 
    FROM penjualan 
    WHERE no_invoice IN ('WEB-20261028-544', 'WEB-20261029-308', 'WEB-20261101-670', 'WEB-20261101-726', 'WEB-20261103-677')
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cancels as $c) {
    echo "Invoice: {$c['no_invoice']} | Status: {$c['status_pesanan']} | ReqBatal: {$c['request_batal']} | Alasan: {$c['alasan_batal']} | ReqAlasan: {$c['request_batal_alasan']}\n";
}

echo "\n=== 6. VERIFIKASI SESI STOK OPNAME HISTORIS ===\n";
$opn = $pdo->query("SELECT kode_sesi, status, catatan FROM sesi_opname WHERE kode_sesi IN ('OPN-20261015-001', 'OPN-20261028-002', 'OPN-20261101-003')")->fetchAll(PDO::FETCH_ASSOC);
foreach ($opn as $o) {
    echo "Sesi: {$o['kode_sesi']} | Status: {$o['status']} | Catatan: {$o['catatan']}\n";
}

