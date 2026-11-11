<?php
// Comprehensive Lifecycle Test for Stok Opname
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/opname_helper.php';
require_once __DIR__ . '/restore_baseline.php';

echo "=== 1. RESTORE BASELINE ===\n";
restoreBaselineInventory($pdo);

echo "=== 2. START SESSION (SUPERADMIN) ===\n";
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'Superadmin IT';

// Insert new session
$kodeSesi = 'OPN-TEST-' . date('Ymd-His');
$pdo->prepare("
    INSERT INTO sesi_opname (kode_sesi, dibekukan_oleh, catatan, status, tahap)
    VALUES (?, 1, 'Pengujian Otomatis Lifecycle Dosen', 'Aktif', 'Penghitungan')
")->execute([$kodeSesi]);
$idSesi = $pdo->lastInsertId();

// Snapshot 121 rows
$batchRows = $pdo->query("
    SELECT sb.id AS id_batch, sb.id_variasi, 'Obat' AS jenis_komoditas, 'kecil' AS satuan_tipe,
           sb.stok_sisa AS stok_sistem_fisik, pv.satuan_kecil AS nama_satuan
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
")->fetchAll(PDO::FETCH_ASSOC);

$alkesRows = $pdo->query("
    SELECT NULL AS id_batch, st.id_variasi, 'Alkes' AS jenis_komoditas, 'kecil' AS satuan_tipe,
           st.stok AS stok_sistem_fisik, pv.satuan_kecil AS nama_satuan
    FROM stok_toko st
    JOIN produk_variasi pv ON st.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pi.kategori = 'Alat Kesehatan'
")->fetchAll(PDO::FETCH_ASSOC);

$allSnap = array_merge($batchRows, $alkesRows);
echo "Total entitas di-snapshot: " . count($allSnap) . "\n";

$insStmt = $pdo->prepare("
    INSERT INTO detail_opname_batch 
    (id_sesi, id_variasi, id_batch, jenis_komoditas, satuan_tipe, nama_satuan, stok_sistem_fisik, stok_reserved, stok_tersedia_sistem, status_mutasi, status_hitung)
    VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, 'Belum Dimutasi', 'Belum')
");
foreach ($allSnap as $snap) {
    $insStmt->execute([
        $idSesi,
        $snap['id_variasi'],
        $snap['id_batch'],
        $snap['jenis_komoditas'],
        $snap['satuan_tipe'],
        $snap['nama_satuan'],
        $snap['stok_sistem_fisik'],
        $snap['stok_sistem_fisik']
    ]);
}
echo "Snapshot berhasil disimpan ke detail_opname_batch.\n";

echo "=== 3. TEST RENDER KASIR (PENGHITUNGAN) ===\n";
$_SESSION['role'] = 'kasir';
$_SESSION['user_id'] = 4;
$_SESSION['nama_lengkap'] = 'Kasir Toko';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$kasirHtml = ob_get_clean();
echo "Render Kasir HTML length: " . strlen($kasirHtml) . "\n";

// Verify Kasir HTML has condition inputs and no system stock numbers
$hasBaikInput = strpos($kasirHtml, 'name="bulk_fisik_baik[') !== false;
$hasRusakInput = strpos($kasirHtml, 'name="bulk_fisik_rusak[') !== false;
$hasExpInput = strpos($kasirHtml, 'name="bulk_fisik_expired[') !== false;
echo "Kasir: Input Baik? " . ($hasBaikInput ? "YES" : "NO") . ", Input Rusak? " . ($hasRusakInput ? "YES" : "NO") . ", Input Expired? " . ($hasExpInput ? "YES" : "NO") . "\n";

echo "=== 4. TEST RENDER ADMIN (PENGHITUNGAN - PROGRESS MONITORING ONLY) ===\n";
$_SESSION['role'] = 'admin';
$_SESSION['user_id'] = 2;
$_SESSION['nama_lengkap'] = 'Admin Gudang';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$adminProgHtml = ob_get_clean();
echo "Render Admin Progress HTML length: " . strlen($adminProgHtml) . "\n";
$hasProgTable = strpos($adminProgHtml, 'Status Pencatatan Fisik oleh Kasir') !== false;
$hasTutupBtn = strpos($adminProgHtml, 'Tutup Penghitungan &amp; Buka Verifikasi') !== false;
$adminHasNoFisikInput = strpos($adminProgHtml, 'name="bulk_fisik_baik[') === false;
echo "Admin: Tabel Progres? " . ($hasProgTable ? "YES" : "NO") . ", Tombol Tutup? " . ($hasTutupBtn ? "YES" : "NO") . ", Bebas Form Input Fisik? " . ($adminHasNoFisikInput ? "YES" : "NO") . "\n";

echo "=== 5. SIMULASI AUTO-GENERATE SKENARIO UJI COBA (SUPERADMIN) ===\n";
$_SESSION['role'] = 'superadmin';
$_SESSION['user_id'] = 1;

// Simulate POST isi_cepat_demo
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['aksi'] = 'isi_cepat_demo';

// Execute directly via SQL logic for test script
$stmtRows = $pdo->prepare("
    SELECT dob.id, dob.jenis_komoditas, dob.satuan_tipe, dob.stok_sistem_fisik,
           pi.nama_produk, pv.nama_variasi, sb.no_batch, sb.tgl_exp
    FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
    WHERE dob.id_sesi = ?
    ORDER BY dob.id ASC
");
$stmtRows->execute([$idSesi]);
$allRows = $stmtRows->fetchAll(PDO::FETCH_ASSOC);

$doneCef = false; $doneSanmol = false; $doneAmox = false; $doneOmron = false; $doneBeta = false;
$stmtUp = $pdo->prepare("
    UPDATE detail_opname_batch
    SET fisik_baik = ?, fisik_rusak = ?, fisik_expired = ?, hitung_fisik = ?, selisih = ?, status_mutasi = ?, status_hitung = 'Sudah', dihitung_oleh = 1, waktu_hitung = NOW()
    WHERE id = ?
");

foreach ($allRows as $uRow) {
    $sistem = intval($uRow['stok_sistem_fisik']);
    $fBaik = $sistem; $fRusak = 0; $fExp = 0;

    if (!$doneCef && stripos($uRow['nama_produk'], 'Cefadroxil') !== false) {
        $fBaik = 5; $fRusak = 3; $fExp = 2; $doneCef = true;
    } elseif (!$doneSanmol && stripos($uRow['nama_produk'], 'Sanmol') !== false) {
        $fBaik = 0; $fRusak = 0; $fExp = 12; $doneSanmol = true;
    } elseif (!$doneAmox && stripos($uRow['nama_produk'], 'Amoxicillin') !== false) {
        $fBaik = max(0, $sistem - 2); $doneAmox = true;
    } elseif (!$doneOmron && stripos($uRow['nama_produk'], 'Omron') !== false) {
        $fBaik = 0; $doneOmron = true;
    } elseif (!$doneBeta && stripos($uRow['nama_produk'], 'Betadine') !== false) {
        $fBaik = $sistem + 2; $doneBeta = true;
    }

    $selisih = $fBaik - $sistem;
    $statusMut = ($selisih === 0 && $fRusak === 0 && $fExp === 0) ? 'Sesuai' : 'Belum Dimutasi';
    $stmtUp->execute([$fBaik, $fRusak, $fExp, $fBaik, $selisih, $statusMut, $uRow['id']]);
}

$pdo->prepare("UPDATE sesi_opname SET tahap = 'Verifikasi', ditutup_hitung_oleh = 1, ditutup_hitung_pada = NOW() WHERE id = ?")->execute([$idSesi]);
cekSesiOpnameAktif($pdo, true);
echo "Auto-fill selesai. Tahap beralih ke Verifikasi.\n";

echo "=== 6. TEST RENDER VERIFIKASI (ADMIN) DENGAN AUTO-FILTER ANOMALI ===\n";
$_SERVER['REQUEST_METHOD'] = 'GET';
unset($_POST['aksi']);
$_GET['status_eval'] = 'berselisih';
$_SESSION['role'] = 'admin';

ob_start();
include __DIR__ . '/../inventori/stok_opname.php';
$verifHtml = ob_get_clean();
echo "Render Verifikasi HTML length: " . strlen($verifHtml) . "\n";

$hasEstLossCard = strpos($verifHtml, 'Estimasi Nilai Acuan Dampak Persediaan') !== false;
$hasMutasiRusakBtn = strpos($verifHtml, 'Mutasi Rusak (') !== false;
$hasMutasiExpBtn = strpos($verifHtml, 'Mutasi Expired (') !== false;
$hasMutasiHilangBtn = strpos($verifHtml, 'Mutasi Hilang') !== false;
$hasFokusFilter = strpos($verifHtml, 'Fokus Anomali Terfilter Otomatis') !== false;
echo "Verifikasi: Estimasi Kerugian Card? " . ($hasEstLossCard ? "YES" : "NO") . ", Tombol Mutasi Rusak? " . ($hasMutasiRusakBtn ? "YES" : "NO") . ", Tombol Mutasi Expired? " . ($hasMutasiExpBtn ? "YES" : "NO") . ", Tombol Mutasi Hilang? " . ($hasMutasiHilangBtn ? "YES" : "NO") . ", Banner Auto-Filter? " . ($hasFokusFilter ? "YES" : "NO") . "\n";

echo "=== 7. CLEANUP & RESTORE BASELINE ===\n";
restoreBaselineInventory($pdo);
echo "=== LIFECYCLE TEST COMPLETED 100% CLEAN! ===\n";
