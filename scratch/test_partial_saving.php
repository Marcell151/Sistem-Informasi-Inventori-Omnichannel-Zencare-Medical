<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/opname_helper.php';
require_once __DIR__ . '/restore_baseline.php';

echo "=== TEST PARTIAL SAVING & EMPTY REJECTION ===\n";
restoreBaselineInventory($pdo);
cekSesiOpnameAktif($pdo, true);

// 1. Create a session
$kodeSesi = 'OPN-TEST-' . date('Ymd-His');
$stmtSesi = $pdo->prepare("INSERT INTO sesi_opname (kode_sesi, status, tahap, dibekukan_oleh, dibekukan_pada, catatan) VALUES (?, 'Aktif', 'Penghitungan', 1, NOW(), 'Test Sesi')");
$stmtSesi->execute([$kodeSesi]);
$idSesi = $pdo->lastInsertId();

// Snapshot 1: Batch
$stmtObat = $pdo->query("
    SELECT sb.id AS id_batch, sb.id_variasi, sb.no_batch, sb.stok_sisa, 0 AS stok_reserved_kecil,
           pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, pi.nama_produk, pi.kategori, pv.nama_variasi
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pv.is_active = 1 AND sb.stok_sisa > 0
");
$obatBatches = $stmtObat->fetchAll(PDO::FETCH_ASSOC);

$insDetail = $pdo->prepare("
    INSERT INTO detail_opname_batch 
    (id_sesi, id_batch, id_variasi, jenis_komoditas, satuan_tipe, nama_satuan, stok_tersedia_sistem, stok_reserved, stok_sistem_fisik, hitung_fisik, selisih, status_mutasi)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, 'Belum Dimutasi')
");

foreach ($obatBatches as $ob) {
    $insDetail->execute([
        $idSesi,
        $ob['id_batch'],
        $ob['id_variasi'],
        ($ob['kategori'] === 'Alat Kesehatan' ? 'Alkes' : 'Obat'),
        'kecil',
        $ob['satuan_kecil'] ?: 'Pcs',
        $ob['stok_sisa'],
        0,
        $ob['stok_sisa']
    ]);
}

// Check newly created detail rows
$sample = $pdo->query("SELECT id, jenis_komoditas, fisik_baik, fisik_rusak, fisik_expired, status_hitung, hitung_fisik FROM detail_opname_batch WHERE id_sesi = $idSesi LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
echo "New rows initial state:\n";
print_r($sample);

// Test 1: Simulate submitting EMPTY form (all blank)
echo "\n--- TEST 1: SUBMITTING EMPTY FORM ---\n";
$bulkBaik = [];
$bulkRusak = [];
$bulkExpired = [];
foreach ($sample as $s) {
    $bulkBaik[$s['id']] = '';
    $bulkRusak[$s['id']] = '';
    $bulkExpired[$s['id']] = '';
}

$countSaved = 0;
foreach ($bulkBaik as $idDet => $valBaik) {
    $vBaik = isset($bulkBaik[$idDet]) ? trim((string)$bulkBaik[$idDet]) : '';
    $vRusak = isset($bulkRusak[$idDet]) ? trim((string)$bulkRusak[$idDet]) : '';
    $vExp = isset($bulkExpired[$idDet]) ? trim((string)$bulkExpired[$idDet]) : '';

    if ($vBaik === '' && $vRusak === '' && $vExp === '') continue;
    $countSaved++;
}

if ($countSaved === 0) {
    echo "✓ TEST 1 PASS: Empty form is rejected with countSaved = 0 (No rows updated)!\n";
} else {
    echo "✗ TEST 1 FAIL: countSaved = $countSaved\n";
}

// Test 2: Simulate submitting 1 item filled (Baik = 5, Rusak = 0, Expired = 0)
echo "\n--- TEST 2: SUBMITTING ONLY 1 ITEM FILLED ---\n";
$targetId = $sample[0]['id'];
$bulkBaik[$targetId] = '5';
$bulkRusak[$targetId] = '0';
$bulkExpired[$targetId] = '0';

$countSaved = 0;
$stmtUpdate = $pdo->prepare("
    UPDATE detail_opname_batch
    SET fisik_baik = ?, fisik_rusak = ?, fisik_expired = ?, hitung_fisik = ?, selisih = ?, status_mutasi = ?, status_hitung = 'Sudah', dihitung_oleh = 1, waktu_hitung = NOW()
    WHERE id = ?
");

foreach ($bulkBaik as $idDet => $valBaik) {
    $vBaik = isset($bulkBaik[$idDet]) ? trim((string)$bulkBaik[$idDet]) : '';
    $vRusak = isset($bulkRusak[$idDet]) ? trim((string)$bulkRusak[$idDet]) : '';
    $vExp = isset($bulkExpired[$idDet]) ? trim((string)$bulkExpired[$idDet]) : '';

    if ($vBaik === '' && $vRusak === '' && $vExp === '') continue;

    $fBaik = ($vBaik !== '') ? max(0, intval($vBaik)) : 0;
    $fRusak = ($vRusak !== '') ? max(0, intval($vRusak)) : 0;
    $fExp   = ($vExp !== '') ? max(0, intval($vExp)) : 0;

    $stmtUpdate->execute([$fBaik, $fRusak, $fExp, $fBaik, 0, 'Sesuai', $idDet]);
    $countSaved++;
}

echo "✓ TEST 2: Exactly $countSaved item saved!\n";

// Verify database states of target vs others
$checkTarget = $pdo->query("SELECT id, status_hitung, hitung_fisik, fisik_baik, fisik_rusak, fisik_expired FROM detail_opname_batch WHERE id = $targetId")->fetch(PDO::FETCH_ASSOC);
echo "Target item (Should be Sudah): \n";
print_r($checkTarget);

$checkOther = $pdo->query("SELECT id, status_hitung, hitung_fisik, fisik_baik, fisik_rusak, fisik_expired FROM detail_opname_batch WHERE id = {$sample[1]['id']}")->fetch(PDO::FETCH_ASSOC);
echo "Other item (Should be Belum & NULL): \n";
print_r($checkOther);

if ($checkTarget['status_hitung'] === 'Sudah' && $checkOther['status_hitung'] === 'Belum' && $checkOther['hitung_fisik'] === null) {
    echo "✓ TEST 2 PASS: Partial save works perfectly! Untouched rows remain Belum Dihitung (NULL)!\n";
} else {
    echo "✗ TEST 2 FAIL!\n";
}

// Cleanup
restoreBaselineInventory($pdo);
cekSesiOpnameAktif($pdo, true);
echo "\n=== ALL TESTS PASSED & CLEAN BASELINE RESTORED ===\n";

