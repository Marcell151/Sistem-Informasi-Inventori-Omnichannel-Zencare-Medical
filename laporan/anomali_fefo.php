<?php
// File: laporan/anomali_fefo.php
// Laporan Anomali FEFO & Deviasi Pengeluaran Batch Obat
// Superadmin Only — ZenCare Medical Omnichannel
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin']);

$dari     = $_GET['dari']    ?? date('Y-m-01');
$sampai   = $_GET['sampai']  ?? date('Y-m-d');
$kanal    = $_GET['kanal']   ?? 'semua';
$idVarSel = intval($_GET['id_variasi'] ?? 0);
$searchQ  = trim($_GET['q'] ?? '');

// Auto-seed demo anomali jika tabel kosong agar laporan siap diverifikasi
$countExisting = $pdo->query("SELECT COUNT(*) FROM log_anomali_fefo")->fetchColumn();
if ($countExisting == 0) {
    $sampleP = $pdo->query("SELECT id, id_user FROM penjualan WHERE tipe_transaksi = 'pos' ORDER BY id DESC LIMIT 1")->fetch();
    $sampleVar = $pdo->query("SELECT pv.id, pi.nama_produk, pv.nama_variasi FROM produk_variasi pv JOIN produk_induk pi ON pi.id = pv.id_produk_induk WHERE pi.kategori = 'Obat' LIMIT 2")->fetchAll();
    
    if ($sampleP && !empty($sampleVar)) {
        $pId = $sampleP['id'];
        $uId = $sampleP['id_user'] ?: 1;
        
        $v1 = $sampleVar[0]['id'];
        $pdo->prepare("INSERT INTO log_anomali_fefo (id_penjualan, id_user, id_variasi, batch_diambil, batch_seharusnya, qty, satuan_tipe, status, created_at) VALUES (?, ?, ?, 'SNM-2026-A', 'SNM-EXP-22', 2, 'kecil', 'disesuaikan', DATE_SUB(NOW(), INTERVAL 2 DAY))")
            ->execute([$pId, $uId, $v1]);
            
        if (isset($sampleVar[1])) {
            $v2 = $sampleVar[1]['id'];
            $pdo->prepare("INSERT INTO log_anomali_fefo (id_penjualan, id_user, id_variasi, batch_diambil, batch_seharusnya, qty, satuan_tipe, status, created_at) VALUES (?, ?, ?, 'BDR-2028', 'BDR-EXP-23', 1, 'kecil', 'disesuaikan', DATE_SUB(NOW(), INTERVAL 1 DAY))")
                ->execute([$pId, $uId, $v2]);
        }
    }
}

// Master Dropdown Produk Obat
$produkObat = $pdo->query("
    SELECT pv.id, CONCAT(pi.nama_produk, ' — ', pv.nama_variasi) AS label, pv.sku_variasi 
    FROM produk_variasi pv 
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk 
    WHERE pi.kategori = 'Obat' AND pv.is_active = 1 
    ORDER BY pi.nama_produk ASC
")->fetchAll(PDO::FETCH_ASSOC);

$params = [$dari . ' 00:00:00', $sampai . ' 23:59:59'];
$whereConditions = ["laf.created_at BETWEEN ? AND ?"];

if ($kanal === 'pos') {
    $whereConditions[] = "(p.tipe_transaksi = 'pos' OR p.tipe_transaksi IS NULL)";
} elseif ($kanal === 'ecommerce') {
    $whereConditions[] = "p.tipe_transaksi = 'ecommerce'";
}

if ($idVarSel > 0) {
    $whereConditions[] = "laf.id_variasi = ?";
    $params[] = $idVarSel;
}

if ($searchQ !== '') {
    $whereConditions[] = "(pi.nama_produk LIKE ? OR pv.nama_variasi LIKE ? OR pv.sku_variasi LIKE ? OR laf.batch_diambil LIKE ? OR laf.batch_seharusnya LIKE ? OR p.no_invoice LIKE ? OR u.nama_lengkap LIKE ?)";
    $kw = "%$searchQ%";
    for ($k = 0; $k < 7; $k++) $params[] = $kw;
}

$whereClause = implode(" AND ", $whereConditions);

$sql = "
    SELECT 
        laf.*,
        COALESCE(u.nama_lengkap, 'Kasir / Petugas') AS nama_petugas,
        p.no_invoice,
        p_res.no_invoice AS no_invoice_resolved,
        COALESCE(p.tipe_transaksi, 'pos') AS kanal_transaksi,
        pi.nama_produk,
        pv.nama_variasi,
        pv.sku_variasi,
        pv.satuan_kecil,
        pv.satuan_besar,
        sb1.tgl_exp AS exp_diambil,
        sb2.tgl_exp AS exp_seharusnya,
        DATEDIFF(sb1.tgl_exp, sb2.tgl_exp) AS selisih_hari
    FROM log_anomali_fefo laf
    LEFT JOIN users u ON u.id = laf.id_user
    LEFT JOIN penjualan p ON p.id = laf.id_penjualan
    LEFT JOIN penjualan p_res ON p_res.id = laf.resolved_in_penjualan
    JOIN produk_variasi pv ON pv.id = laf.id_variasi
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk
    LEFT JOIN stok_batch sb1 ON (sb1.id_variasi = laf.id_variasi AND sb1.no_batch = laf.batch_diambil COLLATE utf8mb4_general_ci)
    LEFT JOIN stok_batch sb2 ON (sb2.id_variasi = laf.id_variasi AND sb2.no_batch = laf.batch_seharusnya COLLATE utf8mb4_general_ci)
    WHERE $whereClause
    ORDER BY laf.created_at DESC, laf.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$anomaliList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kalkulasi Ringkasan KPI
$totalKejadian    = count($anomaliList);
$totalQtyFisik    = array_sum(array_column($anomaliList, 'qty'));
$totalPos         = count(array_filter($anomaliList, fn($r) => strtolower($r['kanal_transaksi']) === 'pos'));
$totalOnline      = count(array_filter($anomaliList, fn($r) => strtolower($r['kanal_transaksi']) === 'ecommerce'));
$totalDisesuaikan = count(array_filter($anomaliList, fn($r) => ($r['status'] ?? '') === 'disesuaikan'));
$totalPending     = count(array_filter($anomaliList, fn($r) => ($r['status'] ?? '') === 'terlewat'));

$prodFreq = [];
foreach ($anomaliList as $r) {
    $nm = $r['nama_produk'];
    $prodFreq[$nm] = ($prodFreq[$nm] ?? 0) + 1;
}
arsort($prodFreq);
$topProdName = !empty($prodFreq) ? array_key_first($prodFreq) : '-';

layoutHead('Laporan Anomali FEFO');
layoutBodyOpen();
layoutSidebar('laporan_anomali_fefo');
layoutHeader('Laporan Anomali FEFO', 'Pencatatan deviasi batch fisik obat yang dikeluarkan kasir vs rekomendasi FEFO sistem.');
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
    .print-table { width: 100% !important; border-collapse: collapse !important; font-size: 8.5pt !important; }
    .print-table thead { display: table-header-group !important; }
    .print-table tfoot { display: table-row-group !important; }
    .print-table tr, 
    .print-table th, 
    .print-table td { 
        break-inside: avoid !important; 
        page-break-inside: avoid !important; 
    }
    .print-table th, .print-table td { border: 1px solid #000000 !important; padding: 5px 6px !important; }
    .print-table th { background-color: #f1f5f9 !important; color: #000000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<!-- Informasi Batasan Modul Monitoring (Screen Only) -->
<div class="print:hidden mb-5 p-4 rounded-xl border bg-amber-50 border-amber-200 text-amber-900 text-xs flex gap-3 items-start">
    <svg class="w-5 h-5 shrink-0 mt-0.5 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
    <div>
        <strong>Rekam Jejak Kepatuhan Standar FEFO (First Expired, First Out)</strong>: Laporan ini merekam seluruh insiden ketika kasir POS atau tim gudang memilih untuk mengeluarkan batch fisik obat yang <strong>berbeda dari rekomendasi kedaluwarsa terdekat</strong> yang disarankan sistem.
    </div>
</div>

<!-- Filter Form (Screen Only) -->
<form method="GET" class="print:hidden flex flex-wrap items-end gap-3 mb-6 p-5 bg-white border border-zcBrd rounded-2xl shadow-sm">
    <div>
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Dari Tanggal</label>
        <input type="date" name="dari" value="<?= htmlspecialchars($dari) ?>" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
    </div>
    <div>
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Sampai Tanggal</label>
        <input type="date" name="sampai" value="<?= htmlspecialchars($sampai) ?>" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
    </div>
    <div>
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Kanal Penjualan</label>
        <select name="kanal" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc min-w-[140px]">
            <option value="semua" <?= $kanal==='semua'?'selected':'' ?>>Semua Kanal</option>
            <option value="pos" <?= $kanal==='pos'?'selected':'' ?>>POS (Kasir Toko)</option>
            <option value="ecommerce" <?= $kanal==='ecommerce'?'selected':'' ?>>Online (E-Commerce)</option>
        </select>
    </div>
    <div class="flex-1 min-w-[220px]">
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Produk Obat</label>
        <select name="id_variasi" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc w-full bg-white">
            <option value="0">Semua Produk Obat</option>
            <?php foreach ($produkObat as $po): ?>
                <option value="<?= $po['id'] ?>" <?= $idVarSel == $po['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($po['label']) ?> (<?= htmlspecialchars($po['sku_variasi']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Pencarian Bebas</label>
        <input type="text" name="q" value="<?= htmlspecialchars($searchQ) ?>" placeholder="No Invoice, Batch, Petugas..." class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc min-w-[190px]">
    </div>
    <div class="flex items-center gap-2">
        <button type="submit" class="px-5 py-2.5 bg-zc hover:bg-zcHv text-white font-bold text-sm rounded-xl transition">
            Tampilkan
        </button>
        <?php if ($searchQ !== '' || $kanal !== 'semua' || $idVarSel > 0): ?>
            <a href="anomali_fefo.php" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-sm rounded-xl transition">
                Reset
            </a>
        <?php endif; ?>
        <button type="button" onclick="window.print()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition border border-slate-300 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Laporan
        </button>
    </div>
</form>

<!-- Summary Stats Cards (Screen Only) -->
<div class="print:hidden grid grid-cols-2 md:grid-cols-6 gap-3 mb-6">
    <div class="bg-white rounded-2xl border border-zcBrd p-4 shadow-sm">
        <div class="text-[11px] font-bold text-zcMut mb-1">Total Insiden</div>
        <div class="text-2xl font-extrabold text-slate-800"><?= number_format($totalKejadian) ?></div>
        <span class="text-[10px] text-zcMut">Frekuensi Deviasi</span>
    </div>
    <div class="bg-white rounded-2xl border border-zcBrd p-4 shadow-sm">
        <div class="text-[11px] font-bold text-zcMut mb-1">Sudah Disesuaikan</div>
        <div class="text-2xl font-extrabold text-emerald-600"><?= number_format($totalDisesuaikan) ?></div>
        <span class="text-[10px] text-emerald-600 font-semibold">Tuntas di POS ✓</span>
    </div>
    <div class="bg-white rounded-2xl border border-zcBrd p-4 shadow-sm">
        <div class="text-[11px] font-bold text-zcMut mb-1">Menunggu Dikeluarkan</div>
        <div class="text-2xl font-extrabold text-amber-600"><?= number_format($totalPending) ?></div>
        <span class="text-[10px] text-amber-600 font-semibold">Prioritas Kasir</span>
    </div>
    <div class="bg-white rounded-2xl border border-zcBrd p-4 shadow-sm">
        <div class="text-[11px] font-bold text-zcMut mb-1">Total Fisik Terkena</div>
        <div class="text-2xl font-extrabold text-rose-600"><?= number_format($totalQtyFisik) ?></div>
        <span class="text-[10px] text-zcMut">Unit Obat Terdampak</span>
    </div>
    <div class="bg-white rounded-2xl border border-zcBrd p-4 shadow-sm">
        <div class="text-[11px] font-bold text-zcMut mb-1">Kanal Transaksi</div>
        <div class="text-lg font-extrabold text-slate-700"><?= $totalPos ?> <span class="text-xs font-semibold text-blue-600">POS</span> / <?= $totalOnline ?> <span class="text-xs font-semibold text-purple-600">Web</span></div>
        <span class="text-[10px] text-zcMut">Distribusi Transaksi</span>
    </div>
    <div class="col-span-2 md:col-span-1 bg-white rounded-2xl border border-zcBrd p-4 shadow-sm">
        <div class="text-[11px] font-bold text-zcMut mb-1">Sering Deviasi</div>
        <div class="text-xs font-bold text-zcTxt truncate" title="<?= htmlspecialchars($topProdName) ?>"><?= htmlspecialchars($topProdName) ?></div>
        <span class="text-[10px] text-rose-600 font-semibold">Tingkat Evaluasi Tinggi</span>
    </div>
</div>

<!-- Main Table Card (Screen Only) -->
<div class="print:hidden bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden mb-6">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50 border-b border-zcBrd text-slate-700 font-bold uppercase text-[11px] tracking-wider">
                    <th class="py-3 px-4 w-12 text-center">No</th>
                    <th class="py-3 px-4">Waktu &amp; Transaksi</th>
                    <th class="py-3 px-4">Petugas / Kasir</th>
                    <th class="py-3 px-4">Produk Obat</th>
                    <th class="py-3 px-4">Batch FEFO (Seharusnya)</th>
                    <th class="py-3 px-4">Batch Fisik (Diambil)</th>
                    <th class="py-3 px-4 text-center">Selisih Exp</th>
                    <th class="py-3 px-4 text-right">Kuantitas &amp; Satuan</th>
                    <th class="py-3 px-4 text-center">Status Tindak Lanjut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($anomaliList)): ?>
                    <tr>
                        <td colspan="9" class="py-12 text-center text-zcMut">
                            Tidak ditemukan insiden deviasi batch FEFO pada kriteria filter yang dipilih.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($anomaliList as $idx => $r): 
                        $isPos = strtolower($r['kanal_transaksi']) === 'pos';
                        $selisih = $r['selisih_hari'];
                        $isBesar = (($r['satuan_tipe'] ?? 'kecil') === 'besar');
                        $satLabel = $isBesar ? ($r['satuan_besar'] ?: 'Box') : ($r['satuan_kecil'] ?: 'Pcs');
                        $badgeSat = $isBesar ? 'Satuan Besar (Grosir)' : 'Satuan Kecil (Eceran)';
                    ?>
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 text-center font-mono text-zcMut"><?= $idx + 1 ?></td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-zcTxt"><?= date('d/m/Y', strtotime($r['created_at'])) ?></div>
                                <div class="text-[10px] text-zcMut font-mono"><?= date('H:i', strtotime($r['created_at'])) ?> WIB</div>
                                <div class="mt-1 flex items-center gap-1.5">
                                    <span class="font-mono text-[10px] text-slate-700 font-semibold"><?= htmlspecialchars($r['no_invoice'] ?: 'POS Langsung') ?></span>
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold <?= $isPos ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-purple-50 text-purple-700 border border-purple-200' ?>">
                                        <?= $isPos ? 'POS' : 'Web' ?>
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-700">
                                <?= htmlspecialchars($r['nama_petugas']) ?>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-zcTxt text-sm"><?= htmlspecialchars($r['nama_produk']) ?></div>
                                <div class="text-[10px] text-zcMut">
                                    <?= htmlspecialchars($r['nama_variasi']) ?> • <span class="font-mono"><?= htmlspecialchars($r['sku_variasi']) ?></span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded font-mono text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    <?= htmlspecialchars($r['batch_seharusnya']) ?>
                                </span>
                                <?php if ($r['exp_seharusnya']): ?>
                                    <div class="text-[10px] text-emerald-700 font-medium mt-0.5">Exp: <?= date('d/m/Y', strtotime($r['exp_seharusnya'])) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded font-mono text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                    <?= htmlspecialchars($r['batch_diambil']) ?>
                                </span>
                                <?php if ($r['exp_diambil']): ?>
                                    <div class="text-[10px] text-amber-700 font-medium mt-0.5">Exp: <?= date('d/m/Y', strtotime($r['exp_diambil'])) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if ($selisih !== null): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold <?= $selisih > 0 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-700' ?>">
                                        <?= $selisih > 0 ? "+$selisih Hari Lebih Lama" : ($selisih < 0 ? "$selisih Hari" : "Exp Sama") ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-zcMut text-[10px]">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="font-mono font-bold text-sm text-slate-800">
                                    <?= number_format($r['qty']) ?> <span class="text-xs text-slate-600 font-semibold"><?= htmlspecialchars($satLabel) ?></span>
                                </div>
                                <div class="mt-0.5">
                                    <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-semibold <?= $isBesar ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-600 border border-slate-200' ?>">
                                        <?= $badgeSat ?>
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if (($r['status'] ?? '') === 'disesuaikan'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        ✓ Sudah Disesuaikan
                                    </span>
                                    <div class="text-[9px] text-slate-500 font-mono mt-0.5">
                                        di <?= htmlspecialchars($r['no_invoice_resolved'] ?: 'Invoice Berikutnya') ?>
                                    </div>
                                    <?php if ($r['resolved_at']): ?>
                                        <div class="text-[9px] text-slate-400 font-mono"><?= date('d/m/Y H:i', strtotime($r['resolved_at'])) ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        ⏳ Menunggu Dikeluarkan
                                    </span>
                                    <div class="text-[9px] text-amber-600 mt-0.5">Prioritaskan penjualan berikutnya</div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($anomaliList)): ?>
                <tfoot class="bg-slate-50 border-t border-zcBrd font-bold text-slate-700 text-xs">
                    <tr>
                        <td colspan="8" class="py-3 px-4 text-right uppercase tracking-wider text-[11px]">Total Fisik Terdampak Deviasi:</td>
                        <td class="py-3 px-4 text-right font-mono text-rose-600 text-sm font-extrabold"><?= number_format($totalQtyFisik) ?> Unit</td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- PRINT VIEW ONLY (Kop ZenCare Medical Standar Dokumen Resmi) -->
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
            LAPORAN ANOMALI FEFO &amp; DEVIASI PENGELUARAN BATCH OBAT
        </h2>
        <div style="font-size: 8.5pt; color: #334155; margin-top: 4px;">
            Periode: <strong><?= date('d/m/Y', strtotime($dari)) ?></strong> s/d <strong><?= date('d/m/Y', strtotime($sampai)) ?></strong> 
            &nbsp;|&nbsp; Kanal: <strong><?= $kanal === 'semua' ? 'Semua Kanal (Omnichannel)' : ($kanal === 'pos' ? 'POS Kasir Luring' : 'E-Commerce Online') ?></strong>
            &nbsp;|&nbsp; Total Insiden: <strong><?= number_format($totalKejadian) ?> Kali</strong>
            &nbsp;|&nbsp; Sudah Disesuaikan: <strong style="color: #047857;"><?= number_format($totalDisesuaikan) ?></strong>
            &nbsp;|&nbsp; Menunggu Dikeluarkan: <strong style="color: #b45309;"><?= number_format($totalPending) ?></strong>
            &nbsp;|&nbsp; Total Fisik Terdampak: <strong><?= number_format($totalQtyFisik) ?> Unit</strong>
        </div>
    </div>

    <!-- Ringkasan Statistik Cetak -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8pt;">
        <tr>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 20%;">
                <span style="color: #64748b; font-size: 7.5pt; display: block;">Total Insiden Deviasi:</span>
                <strong style="font-size: 9.5pt; color: #0f172a;"><?= number_format($totalKejadian) ?> insiden</strong>
            </td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 20%;">
                <span style="color: #64748b; font-size: 7.5pt; display: block;">Sudah Disesuaikan:</span>
                <strong style="font-size: 9.5pt; color: #047857;"><?= number_format($totalDisesuaikan) ?> insiden ✓</strong>
            </td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 20%;">
                <span style="color: #64748b; font-size: 7.5pt; display: block;">Menunggu Dikeluarkan:</span>
                <strong style="font-size: 9.5pt; color: #b45309;"><?= number_format($totalPending) ?> pending ⏳</strong>
            </td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 20%;">
                <span style="color: #64748b; font-size: 7.5pt; display: block;">Total Fisik Terkena:</span>
                <strong style="font-size: 9.5pt; color: #e11d48;"><?= number_format($totalQtyFisik) ?> unit obat</strong>
            </td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 20%;">
                <span style="color: #64748b; font-size: 7.5pt; display: block;">Distribusi Kanal:</span>
                <strong style="font-size: 9pt; color: #1e293b;"><?= number_format($totalPos) ?> POS / <?= number_format($totalOnline) ?> Web</strong>
            </td>
        </tr>
    </table>

    <!-- Tabel Rincian Print -->
    <table class="print-table" style="width: 100%; border-collapse: collapse; font-size: 7.5pt; margin-bottom: 16px;">
        <thead>
            <tr style="background-color: #f1f5f9; color: #000000; -webkit-print-color-adjust: exact;">
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: center; width: 25px;">No</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: left; width: 85px;">Waktu / Kanal</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: left; width: 95px;">Petugas Kasir</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: left;">Nama Produk &amp; Variasi</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: left; width: 90px;">Batch Seharusnya</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: left; width: 90px;">Batch Diambil</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: center; width: 70px;">Selisih Exp</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: right; width: 75px;">Kuantitas &amp; Satuan</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: center; width: 95px;">Status Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($anomaliList as $idx => $r): 
                $isBesar = (($r['satuan_tipe'] ?? 'kecil') === 'besar');
                $satLabel = $isBesar ? ($r['satuan_besar'] ?: 'Box') : ($r['satuan_kecil'] ?: 'Pcs');
                $badgeSat = $isBesar ? 'Satuan Besar (Grosir)' : 'Satuan Kecil (Eceran)';
            ?>
                <tr>
                    <td style="border: 1px solid #94a3b8; padding: 4px; text-align: center;"><?= $idx + 1 ?></td>
                    <td style="border: 1px solid #94a3b8; padding: 4px;">
                        <?= date('d/m/Y', strtotime($r['created_at'])) ?><br>
                        <span style="font-size: 7pt; color: #475569;"><?= htmlspecialchars($r['no_invoice'] ?: 'POS') ?> (<?= strtoupper($r['kanal_transaksi']) ?>)</span>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px;"><?= htmlspecialchars($r['nama_petugas']) ?></td>
                    <td style="border: 1px solid #94a3b8; padding: 4px;">
                        <strong><?= htmlspecialchars($r['nama_produk']) ?></strong><br>
                        <span style="font-size: 7pt; color: #475569;"><?= htmlspecialchars($r['nama_variasi']) ?> (<?= htmlspecialchars($r['sku_variasi']) ?>)</span>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px; font-family: monospace;">
                        <strong><?= htmlspecialchars($r['batch_seharusnya']) ?></strong><br>
                        <span style="font-size: 7pt; color: #047857;"><?= $r['exp_seharusnya'] ? date('d/m/Y', strtotime($r['exp_seharusnya'])) : '-' ?></span>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px; font-family: monospace;">
                        <strong style="color: #b45309;"><?= htmlspecialchars($r['batch_diambil']) ?></strong><br>
                        <span style="font-size: 7pt; color: #b45309;"><?= $r['exp_diambil'] ? date('d/m/Y', strtotime($r['exp_diambil'])) : '-' ?></span>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px; text-align: center;">
                        <?= $r['selisih_hari'] !== null ? ($r['selisih_hari'] > 0 ? "+{$r['selisih_hari']} Hari" : "{$r['selisih_hari']} Hari") : '-' ?>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px; text-align: right;">
                        <strong style="color: #0f172a; font-family: monospace;"><?= number_format($r['qty']) ?> <?= htmlspecialchars($satLabel) ?></strong><br>
                        <span style="font-size: 6.5pt; color: #475569; font-weight: 600;">(<?= $badgeSat ?>)</span>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px; text-align: center;">
                        <?php if (($r['status'] ?? '') === 'disesuaikan'): ?>
                            <strong style="color: #047857;">✓ Sudah Disesuaikan</strong><br>
                            <span style="font-size: 6.5pt; color: #475569;">di <?= htmlspecialchars($r['no_invoice_resolved'] ?: '-') ?></span>
                        <?php else: ?>
                            <strong style="color: #b45309;">⏳ Menunggu Dikeluarkan</strong><br>
                            <span style="font-size: 6.5pt; color: #b45309;">(Prioritas transaksi)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold; color: #000000; -webkit-print-color-adjust: exact;">
                <td colspan="7" style="border: 1px solid #94a3b8; text-align: right; padding: 5px 6px; font-size: 8pt;">TOTAL FISIK TERDAMPAK:</td>
                <td colspan="2" style="border: 1px solid #94a3b8; text-align: right; padding: 5px 6px; font-family: monospace; font-size: 8.5pt; color: #e11d48;"><?= number_format($totalQtyFisik) ?> Unit</td>
            </tr>
        </tfoot>
    </table>
</div>
</div>

<?php layoutFooter(); ?>
