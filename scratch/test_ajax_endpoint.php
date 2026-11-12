<?php
// Test get_demo_counts logic
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/opname_helper.php';

$activeSession = cekSesiOpnameAktif($pdo);
if (!$activeSession) {
    die("No active session\n");
}

$idSesi = $activeSession['id'];
echo "Active session ID: $idSesi\n";

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

echo "Total rows fetched: " . count($allRows) . "\n";

$doneCef = false;
$doneSanmol = false;
$doneAmox = false;
$doneOmron = false;
$doneTermo = false;
$doneBeta = false;

$results = [];
$anomaliCount = 0;
foreach ($allRows as $uRow) {
    $sistem = intval($uRow['stok_sistem_fisik']);
    $fBaik = $sistem;
    $fRusak = 0;
    $fExp = 0;
    $anomaliType = '';

    // 1. Cefadroxil 500mg (Satuan Kecil) -> Rusak 2 Strip
    if (!$doneCef && stripos($uRow['nama_produk'], 'Cefadroxil') !== false && $uRow['satuan_tipe'] === 'kecil') {
        $fBaik = max(0, $sistem - 2);
        $fRusak = 2;
        $fExp = 0;
        $doneCef = true;
        $anomaliType = 'rusak';
    }
    // 2. Sanmol Sirup 60ml -> Expired
    elseif (!$doneSanmol && stripos($uRow['nama_produk'], 'Sanmol') !== false && !empty($uRow['tgl_exp']) && strtotime($uRow['tgl_exp']) < strtotime(date('Y-m-d'))) {
        $fBaik = 0;
        $fRusak = 0;
        $fExp = $sistem;
        $doneSanmol = true;
        $anomaliType = 'expired';
    }
    // 3. Amoxicillin 500mg (Satuan Kecil) -> Hilang 2 Strip
    elseif (!$doneAmox && stripos($uRow['nama_produk'], 'Amoxicillin') !== false && $uRow['satuan_tipe'] === 'kecil') {
        $fBaik = max(0, $sistem - 2);
        $doneAmox = true;
        $anomaliType = 'hilang';
    }
    // 4. Tensimeter Digital Omron -> Hilang 1 Unit
    elseif (!$doneOmron && stripos($uRow['nama_produk'], 'Tensimeter') !== false) {
        $fBaik = max(0, $sistem - 1);
        $doneOmron = true;
        $anomaliType = 'hilang';
    }
    // 5. Termometer Digital Omron -> Rusak 1 Unit
    elseif (!$doneTermo && stripos($uRow['nama_produk'], 'Termometer Digital') !== false) {
        $fBaik = max(0, $sistem - 1);
        $fRusak = 1;
        $doneTermo = true;
        $anomaliType = 'rusak';
    }
    // 6. Betadine Solution 15ml (Satuan Kecil) -> Surplus +2 Botol
    elseif (!$doneBeta && stripos($uRow['nama_produk'], 'Betadine') !== false && $uRow['satuan_tipe'] === 'kecil') {
        $fBaik = $sistem + 2;
        $doneBeta = true;
        $anomaliType = 'surplus';
    }

    if ($anomaliType !== '') {
        $anomaliCount++;
        echo "Anomali found: {$uRow['nama_produk']} [{$uRow['satuan_tipe']}] -> Type: $anomaliType, Baik: $fBaik, Rusak: $fRusak, Exp: $fExp (Sistem: $sistem)\n";
    }

    $results[] = [
        'id' => $uRow['id'],
        'baik' => $fBaik,
        'rusak' => $fRusak,
        'exp' => $fExp,
        'anomali' => ($anomaliType !== ''),
        'jenis' => $anomaliType
    ];
}

echo "Total items: " . count($results) . "\n";
echo "Total anomalies: $anomaliCount (Should be 6)\n";
echo "Total normal items: " . (count($results) - $anomaliCount) . " (Should be 105)\n";

