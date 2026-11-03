<?php
require __DIR__ . '/../config/koneksi.php';

// 1. Dashboard
$stokObatBesar = (int)$pdo->query("
    SELECT COALESCE(SUM(FLOOR(sb.stok_sisa / pv.rasio_konversi)), 0)
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pi.kategori != 'Alat Kesehatan' AND sb.no_batch NOT LIKE '%.%' AND sb.stok_sisa > 0
")->fetchColumn();

$stokAlkesUnit = (int)$pdo->query("
    SELECT COALESCE(SUM(st.stok), 0)
    FROM stok_toko st
    JOIN produk_variasi pv ON st.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pi.kategori = 'Alat Kesehatan' AND st.stok > 0
")->fetchColumn();

$totalStokBesar = $stokObatBesar + $stokAlkesUnit;

$stokEceranObat = (int)$pdo->query("
    SELECT COALESCE(SUM(sb.stok_sisa), 0)
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pi.kategori != 'Alat Kesehatan' AND sb.no_batch LIKE '%.%' AND sb.stok_sisa > 0
")->fetchColumn();

$totalStokKecil = $stokEceranObat;

// 2. Laporan Ketersediaan Stok (run live calculation from the file logic)
$stmt = $pdo->query("
    SELECT
        pi.id AS id_induk,
        pi.kategori,
        pv.id AS id_variasi,
        pv.rasio_konversi,
        COALESCE(sc.stok, 0) AS stok_pcs,
        COALESCE(sb_agg.stok_batch_utuh_pcs, 0) AS stok_batch_utuh_pcs,
        COALESCE(sb_agg.stok_sub_batch_ecer_pcs, 0) AS stok_sub_batch_ecer_pcs
    FROM produk_variasi pv
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk
    LEFT JOIN stok_toko sc ON sc.id_variasi = pv.id
    LEFT JOIN (
        SELECT 
            id_variasi,
            SUM(CASE WHEN no_batch NOT LIKE '%.%' AND stok_sisa > 0 THEN stok_sisa ELSE 0 END) AS stok_batch_utuh_pcs,
            SUM(CASE WHEN no_batch LIKE '%.%' AND stok_sisa > 0 THEN stok_sisa ELSE 0 END) AS stok_sub_batch_ecer_pcs
        FROM stok_batch
        WHERE stok_sisa > 0
        GROUP BY id_variasi
    ) sb_agg ON sb_agg.id_variasi = pv.id
    WHERE pi.is_active = 1 AND pv.is_active = 1
");

$kTotalBoxUtuhAll = 0;
$kTotalEceranKonversiAll = 0;

while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $pcs = intval($r['stok_pcs']);
    $rasio = max(1, intval($r['rasio_konversi'] ?: 1));

    if ($r['kategori'] === 'Obat') {
        if ($rasio > 1) {
            $boxUtuh = (int) floor($r['stok_batch_utuh_pcs'] / $rasio);
            $sisaUtuhPcs = (int) ($r['stok_batch_utuh_pcs'] % $rasio);
            $eceranKonversi = (int) ($r['stok_sub_batch_ecer_pcs'] + $sisaUtuhPcs);
            if ($boxUtuh === 0 && $eceranKonversi === 0 && $pcs > 0) {
                $boxUtuh = (int) floor($pcs / $rasio);
                $eceranKonversi = (int) ($pcs % $rasio);
            }
        } else {
            $boxUtuh = $pcs;
            $eceranKonversi = 0;
        }
    } else {
        $boxUtuh = $pcs;
        $eceranKonversi = 0;
    }

    $kTotalBoxUtuhAll += $boxUtuh;
    $kTotalEceranKonversiAll += $eceranKonversi;
}

echo "=== PERBANDINGAN NILAI SINKRONISASI ===\n";
echo "1. Satuan Besar:\n";
echo "   - Dashboard (index.php)            : " . number_format($totalStokBesar) . " Dus/Unit\n";
echo "   - Laporan Ketersediaan Stok        : " . number_format($kTotalBoxUtuhAll) . " Satuan Besar\n";
echo "   - SINKRON? " . ($totalStokBesar === $kTotalBoxUtuhAll ? "YA (100% Cocok)" : "BEDA!") . "\n\n";

echo "2. Satuan Kecil (Eceran Konversi):\n";
echo "   - Dashboard (index.php)            : " . number_format($totalStokKecil) . " Eceran\n";
echo "   - Laporan Ketersediaan Stok        : " . number_format($kTotalEceranKonversiAll) . " Satuan Kecil\n";
echo "   - SINKRON? " . ($totalStokKecil === $kTotalEceranKonversiAll ? "YA (100% Cocok)" : "BEDA!") . "\n";
