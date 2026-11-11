<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/opname_helper.php';

$activeSession = cekSesiOpnameAktif($pdo);
$idSesi = $activeSession['id'];

// Test 2: Simulate typing values (Sanmol Exp: 2, Rusak: 0; Bodrex Exp: 4, Rusak: 0)
echo "\n--- TEST 2: Typing specific values into expired inputs ---\n";
$_POST['aksi'] = 'simpan_semua_hitung_fisik';
$_POST['bulk_item_ids'] = [2844, 2764];
$_POST['bulk_fisik_baik'] = [2844 => '0', 2764 => '0'];
$_POST['bulk_fisik_rusak'] = [2844 => '0', 2764 => '0'];
$_POST['bulk_fisik_expired'] = [2844 => '2', 2764 => '4'];

$bulkItemIds = $_POST['bulk_item_ids'] ?? [];
$bulkBaik = is_array($_POST['bulk_fisik_baik'] ?? null) ? $_POST['bulk_fisik_baik'] : [];
$bulkRusak = is_array($_POST['bulk_fisik_rusak'] ?? null) ? $_POST['bulk_fisik_rusak'] : [];
$bulkExpired = is_array($_POST['bulk_fisik_expired'] ?? null) ? $_POST['bulk_fisik_expired'] : [];

$allIds = array_unique(array_filter(array_merge(
    is_array($bulkItemIds) ? array_map('intval', $bulkItemIds) : [],
    array_map('intval', array_keys($bulkBaik)),
    array_map('intval', array_keys($bulkRusak)),
    array_map('intval', array_keys($bulkExpired))
)));

$pdo->beginTransaction();
$countSaved = 0;
$stmtCheck = $pdo->prepare("
    SELECT dob.id, dob.stok_sistem_fisik, dob.jenis_komoditas, sb.tgl_exp
    FROM detail_opname_batch dob
    LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
    WHERE dob.id = ? AND dob.id_sesi = ?
");
$stmtUpdate = $pdo->prepare("
    UPDATE detail_opname_batch
    SET fisik_baik = ?,
        fisik_rusak = ?,
        fisik_expired = ?,
        hitung_fisik = ?,
        selisih = ?,
        status_mutasi = ?,
        status_hitung = 'Sudah',
        dihitung_oleh = 1,
        waktu_hitung = NOW()
    WHERE id = ?
");

foreach ($allIds as $idDet) {
    $idDet = intval($idDet);
    if ($idDet <= 0) continue;

    $stmtCheck->execute([$idDet, $idSesi]);
    $rowItem = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    if (!$rowItem) continue;

    $vBaik = isset($bulkBaik[$idDet]) ? trim((string)$bulkBaik[$idDet]) : '';
    $vRusak = isset($bulkRusak[$idDet]) ? trim((string)$bulkRusak[$idDet]) : '';
    $vExp = isset($bulkExpired[$idDet]) ? trim((string)$bulkExpired[$idDet]) : '';

    $isItemExpired = ($rowItem['jenis_komoditas'] === 'Obat' && !empty($rowItem['tgl_exp']) && strtotime($rowItem['tgl_exp']) < strtotime(date('Y-m-d')));

    if ($isItemExpired) {
        $fBaik = 0;
        if ($vExp !== '' || $vRusak !== '') {
            $fRusak = ($vRusak !== '') ? max(0, intval($vRusak)) : 0;
            $fExp = ($vExp !== '') ? max(0, intval($vExp)) : max(0, intval($rowItem['stok_sistem_fisik']) - $fRusak);
        } else {
            $fRusak = 0;
            $fExp = intval($rowItem['stok_sistem_fisik']);
        }
    } else {
        if ($vBaik === '' && $vRusak === '' && $vExp === '') continue;
        $fBaik = ($vBaik !== '') ? max(0, intval($vBaik)) : 0;
        $fRusak = ($vRusak !== '') ? max(0, intval($vRusak)) : 0;
        $fExp = ($vExp !== '') ? max(0, intval($vExp)) : 0;
        if ($rowItem['jenis_komoditas'] === 'Alkes') $fExp = 0;
    }

    $stokSistem = intval($rowItem['stok_sistem_fisik']);
    $selisih = $fBaik - $stokSistem;
    $statusMut = ($selisih === 0 && $fRusak === 0 && $fExp === 0) ? 'Sesuai' : 'Belum Dimutasi';

    $stmtUpdate->execute([$fBaik, $fRusak, $fExp, $fBaik, $selisih, $statusMut, $idDet]);
    $countSaved++;
}

echo "Total items saved: $countSaved\n";
$pdo->commit();

$stmt = $pdo->prepare("
    SELECT dob.id, pi.nama_produk, dob.status_hitung, dob.fisik_baik, dob.fisik_rusak, dob.fisik_expired, dob.selisih
    FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE dob.id IN (2844, 2764)
");
$stmt->execute();
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($res as $r) {
    echo " - Post-save ID {$r['id']}: {$r['nama_produk']} -> Status: {$r['status_hitung']}, Baik: {$r['fisik_baik']}, Rusak: {$r['fisik_rusak']}, Expired: {$r['fisik_expired']}, Selisih: {$r['selisih']}\n";
}
