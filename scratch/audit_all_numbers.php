<?php
require __DIR__ . '/../config/koneksi.php';

echo "=================================================================\n";
echo "           AUDIT KONSISTENSI & SINKRONISASI ANGKA SISTEM         \n";
echo "=================================================================\n\n";

// ── 1. ENTITAS MASTER PRODUK ─────────────────────────────────────
$piDashboard = (int)$pdo->query("SELECT COUNT(*) FROM produk_induk WHERE is_active=1")->fetchColumn();
$pvDashboard = (int)$pdo->query("SELECT COUNT(*) FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE pv.is_active=1 AND pi.is_active=1")->fetchColumn();

$piKetersediaan = (int)$pdo->query("SELECT COUNT(DISTINCT pi.id) FROM produk_induk pi JOIN produk_variasi pv ON pi.id = pv.id_produk_induk WHERE pi.is_active=1 AND pv.is_active=1")->fetchColumn();
$pvKetersediaan = (int)$pdo->query("SELECT COUNT(DISTINCT pv.id) FROM produk_induk pi JOIN produk_variasi pv ON pi.id = pv.id_produk_induk WHERE pi.is_active=1 AND pv.is_active=1")->fetchColumn();

echo "1. MASTER PRODUK & VARIASI:\n";
echo "   - Produk Induk : Dashboard = $piDashboard | Lap. Ketersediaan = $piKetersediaan | SINKRON? " . ($piDashboard === $piKetersediaan ? "YA" : "BEDA") . "\n";
echo "   - Variasi (SKU): Dashboard = $pvDashboard | Lap. Ketersediaan = $pvKetersediaan | SINKRON? " . ($pvDashboard === $pvKetersediaan ? "YA" : "BEDA") . "\n\n";

// ── 2. STOK SATUAN BESAR & SATUAN KECIL ───────────────────────────
// Dashboard calculation:
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

$totalBesarDashboard = $stokObatBesar + $stokAlkesUnit;

$totalKecilDashboard = (int)$pdo->query("
    SELECT COALESCE(SUM(sb.stok_sisa), 0)
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pi.kategori != 'Alat Kesehatan' AND sb.no_batch LIKE '%.%' AND sb.stok_sisa > 0
")->fetchColumn();

$totalFisikPcsToko = (int)$pdo->query("SELECT COALESCE(SUM(stok), 0) FROM stok_toko")->fetchColumn();

// Laporan Ketersediaan calculation (using exact file logic):
$stmt = $pdo->query("
    SELECT
        pi.kategori,
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

$kBesar = 0; $kKecil = 0; $kPcs = 0;
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

    $kBesar += $boxUtuh;
    $kKecil += $eceranKonversi;
    $kPcs += $pcs;
}

echo "2. STOK PERSEDIAAN GUDANG & ETALASE:\n";
echo "   - Satuan Besar : Dashboard = " . number_format($totalBesarDashboard) . " | Lap. Ketersediaan = " . number_format($kBesar) . " | SINKRON? " . ($totalBesarDashboard === $kBesar ? "YA (100% Cocok)" : "BEDA") . "\n";
echo "   - Satuan Kecil : Dashboard = " . number_format($totalKecilDashboard) . " | Lap. Ketersediaan = " . number_format($kKecil) . " | SINKRON? " . ($totalKecilDashboard === $kKecil ? "YA (100% Cocok)" : "BEDA") . "\n";
echo "   - Total Fisik  : Dashboard = " . number_format($totalFisikPcsToko) . " | Lap. Ketersediaan = " . number_format($kPcs) . " | SINKRON? " . ($totalFisikPcsToko === $kPcs ? "YA (100% Cocok)" : "BEDA") . "\n\n";

// ── 3. ITEM KRITIS / HABIS ───────────────────────────────────────
$stokKritisDash = (int)$pdo->query("
    SELECT COUNT(*) FROM (
        SELECT pv.id FROM produk_variasi pv
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        LEFT JOIN stok_batch sb ON sb.id_variasi = pv.id
        LEFT JOIN stok_toko sc ON sc.id_variasi = pv.id
        WHERE pv.is_active=1
        GROUP BY pv.id, pi.kategori, pv.rasio_konversi, pv.stok_minimum_besar, pv.stok_minimum_kecil, sc.stok
        HAVING (pi.kategori != 'Alat Kesehatan' AND (COALESCE(SUM(CASE WHEN sb.no_batch NOT LIKE '%.%' THEN sb.stok_sisa ELSE 0 END), 0) / pv.rasio_konversi) <= pv.stok_minimum_besar)
            OR (CASE WHEN pi.kategori = 'Alat Kesehatan' THEN COALESCE(sc.stok, 0) ELSE COALESCE(SUM(CASE WHEN sb.no_batch LIKE '%.%' THEN sb.stok_sisa ELSE 0 END), 0) END) <= pv.stok_minimum_kecil
    ) as kritis
")->fetchColumn();

// Laporan Ketersediaan: variasi dengan pcs < 10
$stokKritisLaporan = (int)$pdo->query("SELECT COUNT(*) FROM stok_toko sc JOIN produk_variasi pv ON sc.id_variasi = pv.id JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE pv.is_active=1 AND sc.stok < 10")->fetchColumn();

echo "3. MONITORING STOK KRITIS:\n";
echo "   - Stok Kritis Berbasis Ambang Batas Minimum (Dashboard) : $stokKritisDash SKU\n";
echo "   - Stok Kritis Di Bawah 10 Pcs (Lap. Ketersediaan)       : $stokKritisLaporan SKU\n\n";

// ── 4. RETUR PEMBELIAN KE SUPPLIER ───────────────────────────────
$returMenunggu = (int)$pdo->query("SELECT COUNT(*) FROM penerimaan_detail WHERE status_retur = 'menunggu_pengganti'")->fetchColumn();
$returSelesai  = (int)$pdo->query("SELECT COUNT(*) FROM penerimaan_detail WHERE status_retur IN ('selesai_tukar_barang', 'selesai_potong_faktur')")->fetchColumn();

echo "4. MONITORING RETUR KE DISTRIBUTOR/SUPPLIER:\n";
echo "   - Menunggu Penggantian dari Supplier : $returMenunggu item\n";
echo "   - Selesai (Tukar Barang / Potong Faktur) : $returSelesai item\n\n";

// ── 5. LAPORAN PENJUALAN OMNICHANNEL ─────────────────────────────
$transaksiSelesai = (int)$pdo->query("SELECT COUNT(*) FROM penjualan WHERE tipe_transaksi != 'Mutasi Konversi' AND status_pesanan NOT IN ('Dibatalkan')")->fetchColumn();
$penjualanPos     = (int)$pdo->query("SELECT COUNT(*) FROM penjualan WHERE tipe_transaksi = 'pos' AND status_pesanan NOT IN ('Dibatalkan')")->fetchColumn();
$penjualanOnline  = (int)$pdo->query("SELECT COUNT(*) FROM penjualan WHERE tipe_transaksi = 'ecommerce' AND status_pesanan NOT IN ('Dibatalkan')")->fetchColumn();

echo "5. VOLUME TRANSAKSI PENJUALAN OMNICHANNEL:\n";
echo "   - Total Transaksi Aktif : $transaksiSelesai faktur\n";
echo "   - Kasir POS (Luring)    : $penjualanPos transaksi\n";
echo "   - E-Commerce (Daring)   : $penjualanOnline pesanan\n";
echo "=================================================================\n";

