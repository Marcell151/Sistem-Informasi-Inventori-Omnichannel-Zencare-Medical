<?php
// File: laporan/ketersediaan_stok.php – Laporan Monitoring Ketersediaan Fisik Stok Berbasis Produk Induk & Variasi
// Superadmin Only — ZenCare Medical
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin']);

$status_filter   = $_GET['status'] ?? 'semua';
$kategori_filter = $_GET['kategori'] ?? 'semua';
$searchQ         = trim($_GET['q'] ?? '');

$where = ["pi.is_active = 1", "pv.is_active = 1"];
$params = [];

if ($kategori_filter === 'Obat') {
    $where[] = "pi.kategori = 'Obat'";
} elseif ($kategori_filter === 'Alat Kesehatan') {
    $where[] = "pi.kategori = 'Alat Kesehatan'";
}

if ($searchQ !== '') {
    $where[] = "(pi.nama_produk LIKE ? OR pv.nama_variasi LIKE ? OR pv.sku_variasi LIKE ? OR pi.sku_induk LIKE ?)";
    $params[] = "%$searchQ%";
    $params[] = "%$searchQ%";
    $params[] = "%$searchQ%";
    $params[] = "%$searchQ%";
}

$whereClause = implode(" AND ", $where);

try {
    $stmt = $pdo->prepare("
        SELECT
            pi.id AS id_induk,
            pi.nama_produk,
            pi.kategori,
            pi.sku_induk,
            pv.id AS id_variasi,
            pv.nama_variasi,
            pv.sku_variasi,
            pv.satuan_kecil,
            pv.satuan_besar,
            pv.rasio_konversi,
            COALESCE(sc.stok, 0) AS stok_pcs,
            COALESCE(sb_agg.stok_batch_utuh_pcs, 0) AS stok_batch_utuh_pcs,
            COALESCE(sb_agg.stok_sub_batch_ecer_pcs, 0) AS stok_sub_batch_ecer_pcs,
            sb_agg.rincian_batch,
            us_agg.rincian_sn,
            COALESCE(us_agg.total_sn_tersedia, 0) AS total_sn_tersedia
        FROM produk_variasi pv
        JOIN produk_induk pi ON pi.id = pv.id_produk_induk
        LEFT JOIN stok_toko sc ON sc.id_variasi = pv.id
        LEFT JOIN (
            SELECT 
                id_variasi,
                SUM(CASE WHEN no_batch NOT LIKE '%.%' AND stok_sisa > 0 THEN stok_sisa ELSE 0 END) AS stok_batch_utuh_pcs,
                SUM(CASE WHEN no_batch LIKE '%.%' AND stok_sisa > 0 THEN stok_sisa ELSE 0 END) AS stok_sub_batch_ecer_pcs,
                GROUP_CONCAT(CONCAT(no_batch, ' (', stok_sisa, ')') ORDER BY tgl_exp ASC SEPARATOR ', ') AS rincian_batch
            FROM stok_batch
            WHERE stok_sisa > 0
            GROUP BY id_variasi
        ) sb_agg ON sb_agg.id_variasi = pv.id
        LEFT JOIN (
            SELECT 
                id_variasi,
                COUNT(*) AS total_sn_tersedia,
                GROUP_CONCAT(serial_number ORDER BY serial_number ASC SEPARATOR ', ') AS rincian_sn
            FROM unit_serial
            WHERE status = 'Tersedia'
            GROUP BY id_variasi
        ) us_agg ON us_agg.id_variasi = pv.id
        WHERE $whereClause
        ORDER BY 
            CASE WHEN pi.kategori = 'Obat' THEN 1 WHEN pi.kategori = 'Alat Kesehatan' THEN 2 ELSE 3 END ASC,
            pi.nama_produk ASC,
            pv.nama_variasi ASC
    ");
    $stmt->execute($params);
    $rawRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ambil seluruh rincian batch aktif per variasi untuk penataan satuan yang presisi
    $batchMap = [];
    $stmtBatches = $pdo->query("
        SELECT id_variasi, no_batch, stok_sisa, tgl_exp 
        FROM stok_batch 
        WHERE stok_sisa > 0 
        ORDER BY tgl_exp ASC, id ASC
    ");
    while ($bRow = $stmtBatches->fetch(PDO::FETCH_ASSOC)) {
        $batchMap[$bRow['id_variasi']][] = $bRow;
    }
} catch (Exception $e) {
    $rawRows = [];
    $batchMap = [];
}

// Grouping Berbasis Produk Induk & Pemisahan Satuan Besar vs Satuan Kecil yang Sudah Dikonversi
$parentProducts = [];
$totalPcsAll = 0;
$totalBoxUtuhAll = 0;
$totalEceranKonversiAll = 0;
$stokKritisCount = 0;
$totalVariasiCount = 0;

foreach ($rawRows as $r) {
    $pcs = intval($r['stok_pcs']);
    $rasio = max(1, intval($r['rasio_konversi'] ?: 1));

    // Perhitungan terpisah Satuan Besar vs Satuan Kecil yang sudah dikonversi
    if ($r['kategori'] === 'Obat') {
        if ($rasio > 1) {
            $boxUtuh = (int) floor($r['stok_batch_utuh_pcs'] / $rasio);
            $sisaUtuhPcs = (int) ($r['stok_batch_utuh_pcs'] % $rasio);
            $eceranKonversi = (int) ($r['stok_sub_batch_ecer_pcs'] + $sisaUtuhPcs);
            // Fallback jika tidak ada record di stok_batch
            if ($boxUtuh === 0 && $eceranKonversi === 0 && $pcs > 0) {
                $boxUtuh = (int) floor($pcs / $rasio);
                $eceranKonversi = (int) ($pcs % $rasio);
            }
        } else {
            $boxUtuh = $pcs;
            $eceranKonversi = $pcs;
        }
    } else {
        // Alat Kesehatan
        if ($rasio > 1) {
            $boxUtuh = (int) floor($pcs / $rasio);
            $eceranKonversi = (int) ($pcs % $rasio);
        } else {
            $boxUtuh = $pcs;
            $eceranKonversi = $pcs;
        }
    }

    if ($pcs <= 0) {
        $status = 'Habis';
        $statusBadge = 'bg-rose-50 border-rose-200 text-rose-700';
    } elseif ($pcs < 10) {
        $status = 'Kritis';
        $statusBadge = 'bg-amber-50 border-amber-200 text-amber-700';
    } else {
        $status = 'Aman';
        $statusBadge = 'bg-emerald-50 border-emerald-200 text-emerald-700';
    }

    if ($pcs < 10) $stokKritisCount++;

    // Terapkan filter status ketersediaan
    if ($status_filter === 'kritis' && $pcs >= 10) continue;
    if ($status_filter === 'habis' && $pcs > 0) continue;
    if ($status_filter === 'aman' && $pcs < 10) continue;

    $r['stok_box_utuh'] = $boxUtuh;
    $r['stok_eceran_konversi'] = $eceranKonversi;
    $r['status_label'] = $status;
    $r['status_badge'] = $statusBadge;

    // Rincian Batch / SN: Satuan Besar untuk Batch Induk, Satuan Kecil untuk Sub-Batch
    $satuanBesar = $r['satuan_besar'] ?: 'Box';
    $satuanKecil = $r['satuan_kecil'] ?: 'Pcs';

    if ($r['kategori'] === 'Alat Kesehatan' && !empty($r['rincian_sn'])) {
        $r['batch_text'] = 'SN: ' . $r['rincian_sn'];
    } elseif (!empty($batchMap[$r['id_variasi']])) {
        $batchItems = [];
        foreach ($batchMap[$r['id_variasi']] as $b) {
            $isSubBatch = str_contains($b['no_batch'], '.');
            if ($isSubBatch) {
                // Sub-batch hasil konversi selalu dalam satuan kecil
                $batchItems[] = $b['no_batch'] . ' (' . number_format($b['stok_sisa']) . ' ' . $satuanKecil . ')';
            } else {
                // Batch induk utuh ditampilkan dalam satuan besar
                if ($rasio > 1) {
                    $qtyBesar = floor($b['stok_sisa'] / $rasio);
                    $sisaKecil = $b['stok_sisa'] % $rasio;
                    if ($sisaKecil > 0) {
                        $batchItems[] = $b['no_batch'] . ' (' . number_format($qtyBesar) . ' ' . $satuanBesar . ' + ' . number_format($sisaKecil) . ' ' . $satuanKecil . ')';
                    } else {
                        $batchItems[] = $b['no_batch'] . ' (' . number_format($qtyBesar) . ' ' . $satuanBesar . ')';
                    }
                } else {
                    $batchItems[] = $b['no_batch'] . ' (' . number_format($b['stok_sisa']) . ' ' . $satuanKecil . ')';
                }
            }
        }
        $r['batch_text'] = implode(', ', $batchItems);
    } else {
        $r['batch_text'] = '-';
    }

    $idInduk = $r['id_induk'];
    if (!isset($parentProducts[$idInduk])) {
        $parentProducts[$idInduk] = [
            'id_induk'               => $idInduk,
            'nama_produk'            => $r['nama_produk'],
            'kategori'               => $r['kategori'],
            'sku_induk'              => $r['sku_induk'] ?? '',
            'total_pcs'              => 0,
            'total_box_utuh'         => 0,
            'total_eceran_konversi'  => 0,
            'has_kritis'             => false,
            'variasi_list'           => []
        ];
    }

    $parentProducts[$idInduk]['total_pcs'] += $pcs;
    $parentProducts[$idInduk]['total_box_utuh'] += $boxUtuh;
    $parentProducts[$idInduk]['total_eceran_konversi'] += $eceranKonversi;
    if ($pcs < 10) $parentProducts[$idInduk]['has_kritis'] = true;
    $parentProducts[$idInduk]['variasi_list'][] = $r;

    $totalPcsAll += $pcs;
    $totalBoxUtuhAll += $boxUtuh;
    $totalEceranKonversiAll += $eceranKonversi;
    $totalVariasiCount++;
}

$parents = array_values($parentProducts);
$totalInduk = count($parents);

// Pagination per Produk Induk (15 Induk per page pada layar)
$perPage = 15;
$page = max(1, intval($_GET['page'] ?? 1));
$totalPages = max(1, (int)ceil($totalInduk / $perPage));
if ($page > $totalPages) $page = $totalPages;
$parentsPageList = array_slice($parents, ($page - 1) * $perPage, $perPage);

layoutHead('Ketersediaan Stok Fisik');
layoutBodyOpen();
layoutSidebar('laporan_ketersediaan');
layoutHeader('Ketersediaan Stok Fisik', 'Pemantauan persediaan berbasis Produk Induk & Variasi, pemisahan Satuan Besar vs Satuan Kecil Konversi, serta rincian Batch.');
?>

<style>
@media print {
    .print\:hidden { display: none !important; }
    .print\:block { display: block !important; }
    aside, header, nav, .sidebar { display: none !important; }
    @page { 
        size: A4 portrait; 
        margin: 15mm 12mm 18mm 12mm; 
    }
    body, main, .flex-1, .min-w-0 { 
        display: block !important;
        position: static !important;
        width: 100% !important;
        height: auto !important;
        min-height: auto !important;
        overflow: visible !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important; 
        color: #0f172a !important; 
        font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important; 
    }
    .print-table { width: 100% !important; border-collapse: collapse !important; font-size: 8pt !important; }
    .print-table thead { display: table-header-group !important; }
    .print-table tfoot { display: table-row-group !important; }
    .print-table tr, 
    .print-table th, 
    .print-table td { 
        break-inside: avoid !important; 
        page-break-inside: avoid !important; 
    }
    .print-table th, .print-table td { border: 1px solid #94a3b8 !important; padding: 4.5px 6px !important; }
    .print-table th { background-color: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .print-parent-header { background-color: #e2e8f0 !important; font-weight: bold; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<!-- SCREEN VIEW: FILTER FORM -->
<div class="print:hidden bg-white border border-zcBrd rounded-2xl shadow-sm p-5 mb-6">
    <form method="GET" class="flex flex-wrap items-end gap-3.5">
        <div>
            <label class="block text-xs font-semibold text-zcTxt mb-1.5">Kategori</label>
            <select name="kategori" class="text-xs border border-zcBrd rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-zc min-w-[140px]">
                <option value="semua" <?= $kategori_filter==='semua'?'selected':'' ?>>Semua Kategori</option>
                <option value="Obat" <?= $kategori_filter==='Obat'?'selected':'' ?>>Obat-obatan</option>
                <option value="Alat Kesehatan" <?= $kategori_filter==='Alat Kesehatan'?'selected':'' ?>>Alat Kesehatan</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-zcTxt mb-1.5">Status Ketersediaan</label>
            <select name="status" class="text-xs border border-zcBrd rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-zc min-w-[170px]">
                <option value="semua" <?= $status_filter==='semua'?'selected':'' ?>>Semua Status</option>
                <option value="kritis" <?= $status_filter==='kritis'?'selected':'' ?>>Stok Kritis / Menipis (&lt; 10)</option>
                <option value="habis" <?= $status_filter==='habis'?'selected':'' ?>>Stok Habis (0 Pcs)</option>
                <option value="aman" <?= $status_filter==='aman'?'selected':'' ?>>Stok Aman (&ge; 10)</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-zcTxt mb-1.5">Cari Produk / SKU</label>
            <input type="text" name="q" value="<?= htmlspecialchars($searchQ) ?>" placeholder="Nama produk atau SKU..." class="text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 bg-white focus:outline-none focus:border-zc min-w-[200px]">
        </div>
        <div class="flex-1 flex justify-end gap-2">
            <button type="submit" class="px-5 py-2.5 bg-zc hover:bg-zcHv text-white text-xs font-bold rounded-xl transition shadow-xs">
                Tampilkan
            </button>
            <?php if ($kategori_filter !== 'semua' || $status_filter !== 'semua' || $searchQ !== ''): ?>
                <a href="ketersediaan_stok.php" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                    Reset
                </a>
            <?php endif; ?>
            <button type="button" onclick="window.print()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition border border-slate-300 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Laporan (A4)
            </button>
        </div>
    </form>
</div>

<!-- SCREEN VIEW: SUMMARY CARDS -->
<div class="print:hidden grid grid-cols-2 sm:grid-cols-5 gap-3.5 mb-6">
    <div class="bg-white border border-zcBrd rounded-2xl p-4 shadow-sm">
        <span class="text-[11px] font-semibold text-zcMut block mb-1">Total Produk Induk</span>
        <span class="text-xl font-bold text-zcTxt"><?= number_format($totalInduk) ?></span>
        <span class="text-[10px] text-zcMut block mt-0.5">Entitas Utama</span>
    </div>
    <div class="bg-white border border-zcBrd rounded-2xl p-4 shadow-sm">
        <span class="text-[11px] font-semibold text-zcMut block mb-1">Total Variasi Terdaftar</span>
        <span class="text-xl font-bold text-zcTxt"><?= number_format($totalVariasiCount) ?></span>
        <span class="text-[10px] text-zcMut block mt-0.5">SKU Aktif</span>
    </div>
    <div class="bg-white border border-zcBrd rounded-2xl p-4 shadow-sm">
        <span class="text-[11px] font-semibold text-amber-700 block mb-1">Satuan Besar (Belum Konversi)</span>
        <span class="text-xl font-bold text-amber-600"><?= number_format($totalBoxUtuhAll) ?></span>
        <span class="text-[10px] text-amber-800 block mt-0.5">Kemasan Pabrik Utuh</span>
    </div>
    <div class="bg-white border border-zcBrd rounded-2xl p-4 shadow-sm">
        <span class="text-[11px] font-semibold text-emerald-700 block mb-1">Satuan Kecil (Sudah Konversi)</span>
        <span class="text-xl font-bold text-emerald-600"><?= number_format($totalEceranKonversiAll) ?></span>
        <span class="text-[10px] text-emerald-800 block mt-0.5">Siap Jual Kasir (Eceran)</span>
    </div>
    <div class="bg-white border border-zcBrd rounded-2xl p-4 shadow-sm col-span-2 sm:col-span-1">
        <span class="text-[11px] font-semibold text-rose-600 block mb-1">Item Kritis / Habis</span>
        <span class="text-xl font-bold text-rose-700"><?= $stokKritisCount ?></span>
        <span class="text-[10px] text-rose-500 block mt-0.5">Perlu Pengadaan</span>
    </div>
</div>

<!-- SCREEN VIEW: ACCORDION LIST PRODUK INDUK -->
<div class="print:hidden mb-8">
    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
        <div class="text-xs text-zcMut font-medium">
            Menampilkan <strong><?= count($parentsPageList) ?></strong> dari <strong><?= $totalInduk ?></strong> Produk Induk &bull; Klik baris atau tanda &blacktriangledown; untuk melihat rincian variasi &amp; batch
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleAllAccordions(true)" class="px-3 py-1.5 bg-white border border-zcBrd text-zcTxt text-xs font-bold rounded-xl hover:bg-slate-50 transition shadow-2xs">
                &darr; Buka Semua (Expand)
            </button>
            <button type="button" onclick="toggleAllAccordions(false)" class="px-3 py-1.5 bg-white border border-zcBrd text-zcTxt text-xs font-bold rounded-xl hover:bg-slate-50 transition shadow-2xs">
                &uarr; Tutup Semua
            </button>
        </div>
    </div>

    <?php if (empty($parentsPageList)): ?>
        <div class="bg-white border border-zcBrd rounded-2xl p-12 text-center text-zcMut">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
            <p class="font-bold text-sm text-zcTxt mb-1">Tidak ada data persediaan fisik yang sesuai filter.</p>
            <p class="text-xs text-zcMut">Coba ubah kata kunci pencarian, kategori, atau status ketersediaan.</p>
        </div>
    <?php else: ?>
        <div class="space-y-3.5">
            <?php foreach ($parentsPageList as $pIdx => $pi): 
                $katBadge = ($pi['kategori'] === 'Obat') ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-teal-50 border-teal-200 text-teal-700';
                $firstVar = $pi['variasi_list'][0] ?? null;
                $uomBesar = $firstVar['satuan_besar'] ?? 'Box';
                $uomKecil = $firstVar['satuan_kecil'] ?? 'Unit';
            ?>
            <div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden transition duration-150 parent-card">
                
                <!-- Parent Header Bar -->
                <div onclick="toggleAccordion('acc_pi_<?= $pi['id_induk'] ?>')" class="p-4 cursor-pointer hover:bg-slate-50/70 flex items-center justify-between gap-3 select-none">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="px-2.5 py-1 rounded-lg border text-[11px] font-bold shrink-0 <?= $katBadge ?>">
                            <?= htmlspecialchars($pi['kategori']) ?>
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-zcTxt truncate flex items-center gap-2">
                                <span><?= htmlspecialchars($pi['nama_produk']) ?></span>
                                <?php if (!empty($pi['sku_induk'])): ?>
                                    <span class="text-[11px] font-mono font-normal text-zcMut">(<?= htmlspecialchars($pi['sku_induk']) ?>)</span>
                                <?php endif; ?>
                            </h3>
                            <div class="text-[11px] text-zcMut flex items-center gap-2 mt-0.5 flex-wrap">
                                <span><?= count($pi['variasi_list']) ?> variasi SKU</span>
                                <span>&bull;</span>
                                <span>Satuan Besar: <strong class="text-amber-700"><?= number_format($pi['total_box_utuh']) ?> <?= htmlspecialchars($uomBesar) ?></strong></span>
                                <span>&bull;</span>
                                <span>Satuan Kecil: <strong class="text-emerald-700"><?= number_format($pi['total_eceran_konversi']) ?> <?= htmlspecialchars($uomKecil) ?></strong></span>
                                <span>&bull;</span>
                                <span>Total Fisik: <strong class="text-blue-700"><?= number_format($pi['total_pcs']) ?> <?= htmlspecialchars($uomKecil) ?></strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <?php if ($pi['has_kritis']): ?>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 border border-amber-200 text-amber-700">
                                Perlu Perhatian
                            </span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 border border-emerald-200 text-emerald-700">
                                Stok Aman
                            </span>
                        <?php endif; ?>
                        
                        <div id="icon_acc_pi_<?= $pi['id_induk'] ?>" class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 font-bold transition-transform text-xs">
                            &#9660;
                        </div>
                    </div>
                </div>

                <!-- Variations Child Table -->
                <div id="acc_pi_<?= $pi['id_induk'] ?>" class="accordion-content hidden border-t border-zcBrd bg-slate-50/60 p-4">
                    <div class="overflow-x-auto bg-white rounded-xl border border-zcBrd shadow-2xs">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50/80 border-b border-zcBrd text-zcMut font-semibold">
                                <tr>
                                    <th class="py-2.5 px-3">SKU Variasi</th>
                                    <th class="py-2.5 px-3">Nama Variasi &amp; Batch Aktif</th>
                                    <th class="py-2.5 px-3 text-center">Rasio UOM</th>
                                    <th class="py-2.5 px-3 text-right text-amber-700">Satuan Besar (Belum Konversi)</th>
                                    <th class="py-2.5 px-3 text-right text-emerald-700">Satuan Kecil (Sudah Konversi)</th>
                                    <th class="py-2.5 px-3 text-right text-blue-700">Total Fisik (Semua)</th>
                                    <th class="py-2.5 px-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zcBrd/60">
                                <?php foreach ($pi['variasi_list'] as $v): ?>
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-2 px-3 font-mono text-[11px] text-slate-600 font-semibold align-top"><?= htmlspecialchars($v['sku_variasi']) ?></td>
                                    <td class="py-2 px-3 align-top">
                                        <div class="font-bold text-zcTxt"><?= htmlspecialchars($v['nama_variasi']) ?></div>
                                        <?php if ($v['batch_text'] !== '-'): ?>
                                            <div class="text-[10px] font-mono text-slate-500 mt-0.5 bg-slate-50 p-1 rounded border border-slate-200">
                                                <strong class="text-slate-700">Batch/SN:</strong> <?= htmlspecialchars($v['batch_text']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 px-3 text-center text-zcMut font-mono text-[11px] align-top whitespace-nowrap">
                                        1 <?= htmlspecialchars($v['satuan_besar'] ?: 'Box') ?> = <?= $v['rasio_konversi'] ?> <?= htmlspecialchars($v['satuan_kecil'] ?: 'Pcs') ?>
                                    </td>
                                    <td class="py-2 px-3 text-right font-bold text-amber-700 align-top whitespace-nowrap">
                                        <?= number_format($v['stok_box_utuh']) ?> <span class="font-normal text-[10px] text-zcMut"><?= htmlspecialchars($v['satuan_besar'] ?: 'Box') ?></span>
                                        <?php if ($v['rasio_konversi'] > 1 && $v['stok_box_utuh'] > 0): ?>
                                            <div class="text-[10px] font-normal text-slate-500 mt-0.5">(= <?= number_format($v['stok_box_utuh'] * $v['rasio_konversi']) ?> <?= htmlspecialchars($v['satuan_kecil'] ?: 'Pcs') ?>)</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 px-3 text-right font-bold text-emerald-700 align-top whitespace-nowrap">
                                        <?= number_format($v['stok_eceran_konversi']) ?> <span class="font-normal text-[10px] text-zcMut"><?= htmlspecialchars($v['satuan_kecil'] ?: 'Pcs') ?></span>
                                        <?php if ($v['rasio_konversi'] > 1): ?>
                                            <div class="text-[10px] font-normal text-emerald-600 mt-0.5">(Eceran Siap Jual)</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 px-3 text-right font-black text-blue-700 align-top whitespace-nowrap">
                                        <?= number_format($v['stok_pcs']) ?> <span class="font-normal text-[10px] text-zcMut"><?= htmlspecialchars($v['satuan_kecil'] ?: 'Pcs') ?></span>
                                        <div class="text-[10px] font-normal text-blue-500 mt-0.5">(Akumulasi Fisik)</div>
                                    </td>
                                    <td class="py-2 px-3 text-center align-top whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border <?= $v['status_badge'] ?>">
                                            <?= $v['status_label'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination (Screen Only) -->
        <?php if ($totalInduk > 0): ?>
        <div class="mt-6 p-4 bg-white border border-zcBrd rounded-2xl shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <span class="text-zcMut font-medium">
                Menampilkan <?= ($page - 1) * $perPage + 1 ?> – <?= min($page * $perPage, $totalInduk) ?> dari <?= $totalInduk ?> Produk Induk
            </span>
            <div class="flex items-center gap-1.5 flex-wrap">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="px-3 py-1.5 rounded-lg border border-zcBrd bg-white text-zcTxt hover:bg-slate-50 font-semibold transition">&larr; Sebelumnya</a>
                <?php else: ?>
                    <span class="px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-100 text-slate-400 font-semibold cursor-not-allowed">&larr; Sebelumnya</span>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <?php if ($p == 1 || $p == $totalPages || ($p >= $page - 2 && $p <= $page + 2)): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border text-xs font-bold transition <?= $p == $page ? 'bg-zc border-zc text-white shadow-xs' : 'border-zcBrd bg-white text-zcTxt hover:bg-slate-50' ?>"><?= $p ?></a>
                    <?php elseif ($p == 2 || $p == $totalPages - 1): ?>
                        <span class="px-1 text-slate-400 font-bold">...</span>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="px-3 py-1.5 rounded-lg border border-zcBrd bg-white text-zcTxt hover:bg-slate-50 font-semibold transition">Selanjutnya &rarr;</a>
                <?php else: ?>
                    <span class="px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-100 text-slate-400 font-semibold cursor-not-allowed">Selanjutnya &rarr;</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<!-- ======================================================== -->
<!-- PRINT VIEW (A4 Portrait - Seluruh Produk Induk & Variasi Dilebarkan) -->
<!-- ======================================================== -->
<div class="hidden print:block w-full">
    <!-- Kop Surat Resmi Bersama Logo ZenCare -->
    <div style="border-bottom: 2px solid #0f172a; padding-bottom: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <img src="/inventory_zencare/gambar/Logo.png" alt="Logo ZenCare" style="height: 48px; width: auto; object-fit: contain;" onerror="this.src='../gambar/Logo.png'">
            <div>
                <h1 style="font-size: 16pt; font-weight: 800; color: #0f2d5a; margin: 0; line-height: 1.1;">ZENCARE MEDICAL</h1>
                <p style="font-size: 8.5pt; color: #475569; margin: 3px 0 0 0; font-weight: 600;">Distributor Alat Kesehatan dan Obat Omnichannel</p>
            </div>
        </div>
        <div style="text-align: right; font-size: 8pt; color: #64748b; line-height: 1.4;">
            <div style="font-weight: bold; color: #0f2d5a;">Dokumen Resmi Sistem Inventori</div>
            <div>Dicetak: <?= date('d/m/Y H:i') ?> WIB</div>
            <div>Oleh: <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Superadmin') ?></div>
        </div>
    </div>

    <!-- Judul & Metadata Laporan -->
    <div style="text-align: center; margin-bottom: 12px;">
        <h2 style="font-size: 12pt; font-weight: 800; color: #0f172a; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">
            LAPORAN KETERSEDIAAN FISIK PERSEDIAAN &amp; BATCH
        </h2>
        <div style="font-size: 8.5pt; color: #334155; margin-top: 4px;">
            Kategori: <strong><?= htmlspecialchars($kategori_filter === 'semua' ? 'Semua (Obat & Alkes)' : $kategori_filter) ?></strong>
            &nbsp;|&nbsp; Status: <strong><?= htmlspecialchars(ucfirst($status_filter)) ?></strong>
            &nbsp;|&nbsp; Total Produk Induk: <strong><?= $totalInduk ?></strong>
            &nbsp;|&nbsp; Total Variasi: <strong><?= $totalVariasiCount ?> SKU</strong>
        </div>
    </div>

    <!-- Ringkasan Statistik Cetak -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8pt;">
        <tr>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 20%;">
                <span style="color: #64748b; font-size: 7pt; display: block;">Produk Induk:</span>
                <strong style="font-size: 9.5pt; color: #0f172a;"><?= number_format($totalInduk) ?></strong>
            </td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 20%;">
                <span style="color: #64748b; font-size: 7pt; display: block;">Variasi SKU:</span>
                <strong style="font-size: 9.5pt; color: #0f172a;"><?= number_format($totalVariasiCount) ?> SKU</strong>
            </td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 20%;">
                <span style="color: #64748b; font-size: 7pt; display: block;">Satuan Besar (Belum Konversi):</span>
                <strong style="font-size: 9.5pt; color: #b45309;"><?= number_format($totalBoxUtuhAll) ?> Satuan Besar</strong>
            </td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 20%;">
                <span style="color: #64748b; font-size: 7pt; display: block;">Satuan Kecil (Sudah Konversi):</span>
                <strong style="font-size: 9.5pt; color: #047857;"><?= number_format($totalEceranKonversiAll) ?> Satuan Kecil</strong>
            </td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 20%;">
                <span style="color: #64748b; font-size: 7pt; display: block;">Total Fisik Terkecil:</span>
                <strong style="font-size: 9.5pt; color: #1d4ed8;"><?= number_format($totalPcsAll) ?> Unit Fisik</strong>
            </td>
        </tr>
    </table>

    <!-- Tabel Lengkap Hierarkis Cetak -->
    <table class="print-table" style="width: 100%; border-collapse: collapse; font-size: 7.5pt;">
        <thead>
            <tr style="background-color: #f1f5f9;">
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: center; width: 25px;">No</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: left;">Produk Induk &amp; Variasi</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: center; width: 65px;">SKU</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: left; width: 140px;">No. Batch / Sub-Batch / SN</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: center; width: 75px;">Rasio UOM</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: right; width: 65px;">Satuan Besar</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: right; width: 65px;">Satuan Kecil</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: right; width: 65px;">Total Fisik</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: center; width: 50px;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($parents)): ?>
            <tr>
                <td colspan="9" style="border: 1px solid #94a3b8; padding: 14px; text-align: center; color: #64748b; font-style: italic;">
                    Tidak ada data persediaan fisik yang sesuai filter.
                </td>
            </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($parents as $pi): 
                    $firstVarP = $pi['variasi_list'][0] ?? null;
                    $pUomBesar = $firstVarP['satuan_besar'] ?? 'Box';
                    $pUomKecil = $firstVarP['satuan_kecil'] ?? 'Unit';
                ?>
                    <!-- Baris Header Produk Induk -->
                    <tr class="print-parent-header" style="background-color: #f1f5f9; font-weight: bold;">
                        <td style="border: 1px solid #94a3b8; padding: 4px; text-align: center;"><?= $no++ ?></td>
                        <td colspan="4" style="border: 1px solid #94a3b8; padding: 4px 6px;">
                            <strong><?= htmlspecialchars($pi['nama_produk']) ?></strong>
                            <?php if (!empty($pi['sku_induk'])): ?>
                                <span style="font-family: monospace; font-size: 7pt; font-weight: normal; color: #475569;">[<?= htmlspecialchars($pi['sku_induk']) ?>]</span>
                            <?php endif; ?>
                            <span style="font-size: 7pt; font-weight: normal; color: #64748b;">(<?= htmlspecialchars($pi['kategori']) ?> &bull; <?= count($pi['variasi_list']) ?> Variasi)</span>
                        </td>
                        <td style="border: 1px solid #94a3b8; padding: 4px; text-align: right; color: #b45309;">
                            <?= number_format($pi['total_box_utuh']) ?> <?= htmlspecialchars($pUomBesar) ?>
                        </td>
                        <td style="border: 1px solid #94a3b8; padding: 4px; text-align: right; color: #047857;">
                            <?= number_format($pi['total_eceran_konversi']) ?> <?= htmlspecialchars($pUomKecil) ?>
                        </td>
                        <td style="border: 1px solid #94a3b8; padding: 4px; text-align: right; color: #1d4ed8;">
                            <?= number_format($pi['total_pcs']) ?> <?= htmlspecialchars($pUomKecil) ?>
                        </td>
                        <td style="border: 1px solid #94a3b8; padding: 4px; text-align: center; font-size: 7pt;">
                            <?= $pi['has_kritis'] ? '<span style="color:#b45309;font-weight:bold;">Kritis</span>' : '<span style="color:#047857;">Aman</span>' ?>
                        </td>
                    </tr>

                    <!-- Baris Variasi Anak di Bawah Produk Induk -->
                    <?php foreach ($pi['variasi_list'] as $v): ?>
                    <tr>
                        <td style="border: 1px solid #94a3b8; padding: 3px; text-align: center; color: #94a3b8;">&bull;</td>
                        <td style="border: 1px solid #94a3b8; padding: 3px 6px 3px 18px;">
                            &bull; <?= htmlspecialchars($v['nama_variasi']) ?>
                        </td>
                        <td style="border: 1px solid #94a3b8; padding: 3px; text-align: center; font-family: monospace; font-size: 7pt;">
                            <?= htmlspecialchars($v['sku_variasi']) ?>
                        </td>
                        <td style="border: 1px solid #94a3b8; padding: 3px; font-family: monospace; font-size: 6.5pt; color: #334155;">
                            <?= htmlspecialchars($v['batch_text']) ?>
                        </td>
                        <td style="border: 1px solid #94a3b8; padding: 3px; text-align: center; font-family: monospace; font-size: 7pt; color: #475569;">
                            1 <?= htmlspecialchars($v['satuan_besar'] ?: 'Box') ?> = <?= $v['rasio_konversi'] ?> <?= htmlspecialchars($v['satuan_kecil'] ?: 'Pcs') ?>
                        </td>
                        <td style="border: 1px solid #94a3b8; padding: 3px; text-align: right;">
                            <?= number_format($v['stok_box_utuh']) ?> <span style="font-size: 6.5pt; color: #64748b;"><?= htmlspecialchars($v['satuan_besar'] ?: 'Box') ?></span>
                        </td>
                        <td style="border: 1px solid #94a3b8; padding: 3px; text-align: right;">
                            <?= number_format($v['stok_eceran_konversi']) ?> <span style="font-size: 6.5pt; color: #64748b;"><?= htmlspecialchars($v['satuan_kecil'] ?: 'Pcs') ?></span>
                        </td>
                        <td style="border: 1px solid #94a3b8; padding: 3px; text-align: right; font-weight: bold; color: #1d4ed8;">
                            <?= number_format($v['stok_pcs']) ?> <span style="font-size: 6.5pt; color: #64748b;"><?= htmlspecialchars($v['satuan_kecil'] ?: 'Pcs') ?></span>
                        </td>
                        <td style="border: 1px solid #94a3b8; padding: 3px; text-align: center; font-size: 6.5pt; font-weight: 600;">
                            <?= $v['status_label'] ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($parents)): ?>
        <tfoot>
            <tr style="background: #f8fafc; font-weight: bold;">
                <td colspan="5" style="border: 1px solid #94a3b8; padding: 5px; text-align: right;">TOTAL PERSEDIAAN KESELURUHAN:</td>
                <td style="border: 1px solid #94a3b8; padding: 5px; text-align: right; color: #b45309;"><?= number_format($totalBoxUtuhAll) ?> Satuan Besar</td>
                <td style="border: 1px solid #94a3b8; padding: 5px; text-align: right; color: #047857;"><?= number_format($totalEceranKonversiAll) ?> Satuan Kecil</td>
                <td style="border: 1px solid #94a3b8; padding: 5px; text-align: right; color: #1d4ed8;"><?= number_format($totalPcsAll) ?> Unit Fisik</td>
                <td style="border: 1px solid #94a3b8; padding: 5px; text-align: center;">-</td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>

    <!-- Footer Dokumen Resmi Cetak -->
    <div style="margin-top: 14px; display: flex; justify-content: space-between; font-size: 7.5pt; color: #64748b; border-top: 1px solid #cbd5e1; padding-top: 4px; page-break-inside: avoid;">
        <span>ZenCare Medical Inventory &bull; Laporan Ketersediaan Fisik &amp; Pelacakan Batch</span>
        <span>Dokumen Resmi Sistem Inventori ZenCare Medical</span>
    </div>
</div>

<script>
function toggleAccordion(id) {
    const el = document.getElementById(id);
    const icon = document.getElementById('icon_' + id);
    if (!el) return;
    
    if (el.classList.contains('hidden')) {
        el.classList.remove('hidden');
        if (icon) icon.style.transform = 'rotate(180deg)';
    } else {
        el.classList.add('hidden');
        if (icon) icon.style.transform = 'rotate(0deg)';
    }
}

function toggleAllAccordions(show) {
    document.querySelectorAll('.accordion-content').forEach(el => {
        const id = el.id;
        const icon = document.getElementById('icon_' + id);
        if (show) {
            el.classList.remove('hidden');
            if (icon) icon.style.transform = 'rotate(180deg)';
        } else {
            el.classList.add('hidden');
            if (icon) icon.style.transform = 'rotate(0deg)';
        }
    });
}
</script>

<?php layoutFooter(); ?>
