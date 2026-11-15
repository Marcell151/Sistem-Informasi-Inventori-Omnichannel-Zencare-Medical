<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$idSesi = 40;
$rows = $pdo->query("
    SELECT dob.*, pv.rasio_konversi, pv.harga_jual_kecil, pv.harga_jual_besar,
           (SELECT pd.harga_beli FROM penerimaan_detail pd WHERE pd.id_variasi = pv.id ORDER BY pd.id DESC LIMIT 1) AS last_harga_beli
    FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    WHERE dob.id_sesi = $idSesi
")->fetchAll(PDO::FETCH_ASSOC);

$totalEstLoss = 0;
$grandTotalKerugian = 0;
foreach ($rows as $r) {
    $rasio = max(1, intval($r['rasio_konversi'] ?? 1));
    $hBeli = floatval($r['last_harga_beli'] ?? 0);
    if ($hBeli <= 0) $hBeli = floatval($r['harga_jual_besar'] ?? 0) * 0.8;
    if ($hBeli <= 0) $hBeli = floatval($r['harga_jual_kecil'] ?? 0) * $rasio * 0.8;
    if ($hBeli <= 0) $hBeli = 15000;
    $hargaSatuan = ($r['satuan_tipe'] === 'besar') ? $hBeli : round($hBeli / $rasio);

    $fRusak = intval($r['fisik_rusak'] ?? 0);
    $fExp   = intval($r['fisik_expired'] ?? 0);
    $sel    = intval($r['selisih'] ?? 0);

    $lossR = $fRusak * $hargaSatuan;
    $lossE = $fExp * $hargaSatuan;
    $lossH = ($sel < 0) ? max(0, abs($sel) - $fRusak - $fExp) * $hargaSatuan : 0;
    $totalEstLoss += ($lossR + $lossE + $lossH);

    $subKerugian = ($sel < 0) ? abs($sel) * $hargaSatuan : 0;
    $grandTotalKerugian += $subKerugian;

    if ($fRusak > 0 || $fExp > 0 || $sel != 0) {
        echo "id={$r['id']}, var={$r['id_variasi']}, sel=$sel, rusak=$fRusak, exp=$fExp, hBeli={$r['last_harga_beli']}, fallback=$hBeli, satuan=$hargaSatuan, est=" . ($lossR+$lossE+$lossH) . ", sub=$subKerugian\n";
    }
}
echo "totalEstLoss=$totalEstLoss, grandTotalKerugian=$grandTotalKerugian\n";

