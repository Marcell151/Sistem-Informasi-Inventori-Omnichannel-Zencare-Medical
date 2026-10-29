<?php
// File: laporan/penerimaan_barang.php
// Laporan Penerimaan Barang & Audit Retur Parsial (Inbound Omnichannel)
// Superadmin Only — ZenCare Medical Omnichannel
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin']);

// Default rentang tanggal: 1 bulan ke belakang s/d hari ini agar riwayat terkini langsung terlihat
$dari        = $_GET['dari']         ?? date('Y-m-01', strtotime('-1 month'));
$sampai      = $_GET['sampai']       ?? date('Y-m-d');
$sumber      = $_GET['sumber']       ?? 'semua';
$idSupplier  = intval($_GET['id_supplier'] ?? 0);
$statusRetur = $_GET['status_retur'] ?? 'semua'; // semua, retur, sempurna
$searchQ     = trim($_GET['q'] ?? '');

// Master Dropdown Supplier
$supplierList = $pdo->query("SELECT id, nama FROM supplier WHERE is_active = 1 ORDER BY nama ASC")->fetchAll(PDO::FETCH_ASSOC);

// Bangun query filter
$params = [$dari, $sampai];
$whereHeader = ["ps.tanggal_terima BETWEEN ? AND ?"];

if ($sumber === 'PO') {
    $whereHeader[] = "ps.sumber = 'PO'";
} elseif ($sumber === 'Pembelian Langsung') {
    $whereHeader[] = "ps.sumber = 'Pembelian Langsung'";
}

if ($idSupplier > 0) {
    $whereHeader[] = "ps.id_supplier = ?";
    $params[] = $idSupplier;
}

if ($searchQ !== '') {
    $whereHeader[] = "(ps.no_referensi LIKE ? OR s.nama LIKE ? OR ps.catatan LIKE ? OR u.nama_lengkap LIKE ? OR EXISTS (
        SELECT 1 FROM penerimaan_detail pd_sub 
        JOIN produk_variasi pv_sub ON pv_sub.id = pd_sub.id_variasi
        JOIN produk_induk pi_sub ON pi_sub.id = pv_sub.id_produk_induk
        WHERE pd_sub.id_penerimaan = ps.id 
        AND (pi_sub.nama_produk LIKE ? OR pv_sub.nama_variasi LIKE ? OR pv_sub.sku_variasi LIKE ? OR pd_sub.no_batch LIKE ?)
    ))";
    $kw = "%$searchQ%";
    for ($k = 0; $k < 8; $k++) $params[] = $kw;
}

$whereClause = implode(" AND ", $whereHeader);

// Query Dokumen Penerimaan Header
$sqlHeader = "
    SELECT 
        ps.id,
        ps.no_referensi,
        ps.sumber,
        ps.id_supplier,
        ps.tanggal_terima,
        ps.catatan,
        ps.file_nota,
        ps.created_at,
        COALESCE(s.nama, 'Kulakan') AS nama_supplier,
        COALESCE(u.nama_lengkap, 'Petugas Gudang') AS nama_petugas
    FROM penerimaan_stok ps
    LEFT JOIN supplier s ON s.id = ps.id_supplier
    LEFT JOIN users u ON u.id = ps.dibuat_oleh
    WHERE $whereClause
    ORDER BY ps.tanggal_terima DESC, ps.id DESC
";

$stmtHeader = $pdo->prepare($sqlHeader);
$stmtHeader->execute($params);
$rawHeaders = $stmtHeader->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua detail untuk dokumen-dokumen yang cocok
$docIds = array_column($rawHeaders, 'id');
$detailMap = [];

if (!empty($docIds)) {
    $inPlaceholders = implode(',', array_fill(0, count($docIds), '?'));
    $sqlDetail = "
        SELECT 
            pd.id,
            pd.id_penerimaan,
            pd.id_variasi,
            COALESCE(pd.qty_pesan, pd.qty_terima) AS qty_pesan,
            pd.qty_terima,
            COALESCE(pd.qty_rusak, 0) AS qty_rusak,
            COALESCE(pd.harga_beli, 0) AS harga_beli,
            pd.no_batch,
            pd.tgl_exp,
            pd.status_retur,
            pd.qty_pengganti_diterima,
            pd.no_batch_pengganti,
            pd.tgl_exp_pengganti,
            pd.tgl_selesai_retur,
            pd.catatan_retur,
            pi.nama_produk,
            pi.kategori,
            pv.nama_variasi,
            pv.sku_variasi,
            COALESCE(pv.satuan_besar, pv.satuan_kecil, 'Pcs') AS satuan_nama
        FROM penerimaan_detail pd
        JOIN produk_variasi pv ON pv.id = pd.id_variasi
        JOIN produk_induk pi ON pi.id = pv.id_produk_induk
        WHERE pd.id_penerimaan IN ($inPlaceholders)
        ORDER BY pd.id ASC
    ";
    $stmtDetail = $pdo->prepare($sqlDetail);
    $stmtDetail->execute($docIds);
    $allDetails = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);

    foreach ($allDetails as $d) {
        $detailMap[$d['id_penerimaan']][] = $d;
    }
}

// Filter berdasarkan Status Retur jika dipilih
$penerimaanList = [];
$totalDokumen = 0;
$totalQtyPesan = 0;
$totalQtyTerima = 0;
$totalQtyRusak = 0;
$totalNilaiBeli = 0;
$countAdaRetur = 0;

foreach ($rawHeaders as $h) {
    $items = $detailMap[$h['id']] ?? [];
    $docHasRetur = false;
    $docHasPending = false;
    $docHasResolved = false;
    $docTotalPesan = 0;
    $docTotalTerima = 0;
    $docTotalRusak = 0;
    $docTotalNilai = 0;

    foreach ($items as $item) {
        $docTotalPesan += (int)$item['qty_pesan'];
        $docTotalTerima += (int)$item['qty_terima'];
        $docTotalRusak += (int)$item['qty_rusak'];
        $docTotalNilai += ((float)$item['qty_terima'] * (float)$item['harga_beli']);
        if ((int)$item['qty_rusak'] > 0) {
            $docHasRetur = true;
            $stRetur = $item['status_retur'] ?? 'menunggu_pengganti';
            if ($stRetur === 'menunggu_pengganti') {
                $docHasPending = true;
            } elseif (in_array($stRetur, ['selesai_tukar_barang', 'selesai_potong_faktur'])) {
                $docHasResolved = true;
            }
        }
    }

    if ($statusRetur === 'retur' && !$docHasRetur) {
        continue;
    }
    if ($statusRetur === 'sempurna' && $docHasRetur) {
        continue;
    }
    if ($statusRetur === 'menunggu' && !$docHasPending) {
        continue;
    }
    if ($statusRetur === 'selesai' && (!$docHasResolved || $docHasPending)) {
        continue;
    }

    $h['items'] = $items;
    $h['has_retur'] = $docHasRetur;
    $h['has_pending'] = $docHasPending;
    $h['has_resolved'] = $docHasResolved;
    $h['total_pesan'] = $docTotalPesan;
    $h['total_terima'] = $docTotalTerima;
    $h['total_rusak'] = $docTotalRusak;
    $h['total_nilai'] = $docTotalNilai;

    $penerimaanList[] = $h;

    $totalDokumen++;
    $totalQtyPesan += $docTotalPesan;
    $totalQtyTerima += $docTotalTerima;
    $totalQtyRusak += $docTotalRusak;
    $totalNilaiBeli += $docTotalNilai;
    if ($docHasRetur) {
        $countAdaRetur++;
    }
}

layoutHead('Laporan Penerimaan Barang');
layoutBodyOpen();
layoutSidebar('laporan_penerimaan');
layoutHeader('Laporan Penerimaan Barang & Retur Parsial', 'Rekam dokumen inbound barang masuk (PO Distributor Resmi & Kulakan) serta verifikasi retur rusak.');
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
    .print-table th, .print-table td { border: 1px solid #000000 !important; padding: 4.5px 6px !important; }
    .print-table th { background-color: #f1f5f9 !important; color: #000000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<!-- Informasi Batasan Modul (Screen Only) -->
<div class="print:hidden mb-5 p-4 rounded-xl border bg-blue-50 border-blue-200 text-blue-800 text-xs flex gap-3 items-start">
    <svg class="w-5 h-5 shrink-0 mt-0.5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
    <div>
        <strong>Riwayat Logistik Inbound & Retur Parsial</strong>: Laporan ini menyajikan pencatatan resmi seluruh faktur penerimaan barang dari <strong>PO Distributor Resmi</strong> maupun <strong>Pembelian Langsung (Kulakan Toko Lain)</strong> secara permanen, mencakup selisih kuantitas rusak (*retur parsial*) dan nilai pembelian riil.
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
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Sumber Pengadaan</label>
        <select name="sumber" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc min-w-[150px]">
            <option value="semua" <?= $sumber === 'semua' ? 'selected' : '' ?>>Semua Sumber</option>
            <option value="PO" <?= $sumber === 'PO' ? 'selected' : '' ?>>PO (Distributor Resmi)</option>
            <option value="Pembelian Langsung" <?= $sumber === 'Pembelian Langsung' ? 'selected' : '' ?>>Kulakan (Toko Lain)</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Distributor</label>
        <select name="id_supplier" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc min-w-[180px]">
            <option value="0">Semua Distributor</option>
            <?php foreach ($supplierList as $sup): ?>
                <option value="<?= $sup['id'] ?>" <?= $idSupplier === (int)$sup['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($sup['nama']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Status Retur</label>
        <select name="status_retur" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc min-w-[160px]">
            <option value="semua" <?= $statusRetur === 'semua' ? 'selected' : '' ?>>Semua Status</option>
            <option value="sempurna" <?= $statusRetur === 'sempurna' ? 'selected' : '' ?>>Lengkap Sempurna (100%)</option>
            <option value="menunggu" <?= $statusRetur === 'menunggu' ? 'selected' : '' ?>>Menunggu Pengganti</option>
            <option value="selesai" <?= $statusRetur === 'selesai' ? 'selected' : '' ?>>Retur Selesai</option>
            <option value="retur" <?= $statusRetur === 'retur' ? 'selected' : '' ?>>Semua Ada Retur</option>
        </select>
    </div>
    <div class="flex-1 min-w-[180px]">
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Pencarian Bebas</label>
        <input type="text" name="q" value="<?= htmlspecialchars($searchQ) ?>" placeholder="No Ref, Produk, Batch..." class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc w-full">
    </div>
    <div class="flex items-center gap-2">
        <button type="submit" class="px-5 py-2.5 bg-zc hover:bg-zcHv text-white font-bold text-sm rounded-xl transition">
            Tampilkan
        </button>
        <?php if ($searchQ !== '' || $sumber !== 'semua' || $idSupplier > 0 || $statusRetur !== 'semua'): ?>
            <a href="penerimaan_barang.php" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-sm rounded-xl transition">
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
<div class="print:hidden grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-zcBrd p-5 shadow-sm">
        <div class="text-xs font-bold text-zcMut mb-1">Total Faktur Inbound</div>
        <div class="text-2xl font-extrabold text-zcTxt"><?= number_format($totalDokumen) ?></div>
        <span class="text-[10px] text-zcMut">Dokumen Masuk</span>
    </div>
    <div class="bg-white rounded-2xl border border-zcBrd p-5 shadow-sm">
        <div class="text-xs font-bold text-zcMut mb-1">Total Item Dipesan</div>
        <div class="text-2xl font-extrabold text-sky-600"><?= number_format($totalQtyPesan) ?></div>
        <span class="text-[10px] text-zcMut">Kuantitas Order Awal</span>
    </div>
    <div class="bg-white rounded-2xl border border-zcBrd p-5 shadow-sm">
        <div class="text-xs font-bold text-zcMut mb-1">Diterima Baik</div>
        <div class="text-2xl font-extrabold text-emerald-600"><?= number_format($totalQtyTerima) ?></div>
        <span class="text-[10px] text-zcMut">Masuk ke Stok Fisik</span>
    </div>
    <div class="bg-white rounded-2xl border border-zcBrd p-5 shadow-sm <?= $totalQtyRusak > 0 ? 'bg-rose-50/50 border-rose-200' : '' ?>">
        <div class="text-xs font-bold <?= $totalQtyRusak > 0 ? 'text-rose-700' : 'text-zcMut' ?> mb-1">Rusak / Retur Parsial</div>
        <div class="text-2xl font-extrabold <?= $totalQtyRusak > 0 ? 'text-rose-600' : 'text-slate-700' ?>"><?= number_format($totalQtyRusak) ?></div>
        <span class="text-[10px] <?= $totalQtyRusak > 0 ? 'text-rose-600 font-semibold' : 'text-zcMut' ?>"><?= $countAdaRetur ?> Dokumen Terdampak</span>
    </div>
    <div class="col-span-2 md:col-span-1 bg-white rounded-2xl border border-zcBrd p-5 shadow-sm">
        <div class="text-xs font-bold text-indigo-700 mb-1">Total Nilai Pembelian</div>
        <div class="text-lg font-black text-indigo-700 leading-tight">Rp <?= number_format($totalNilaiBeli, 0, ',', '.') ?></div>
        <span class="text-[10px] text-zcMut">Fisik Diterima Bersih</span>
    </div>
</div>

<!-- Main Content (Screen Only): Dokumen Inbound -->
<div class="print:hidden space-y-5 mb-8">
    <?php if (empty($penerimaanList)): ?>
        <div class="bg-white p-12 rounded-2xl border border-zcBrd text-center space-y-3">
            <div class="w-12 h-12 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            </div>
            <h3 class="text-sm font-bold text-zcTxt">Tidak Ada Data Penerimaan Ditemukan</h3>
            <p class="text-xs text-zcMut max-w-md mx-auto">
                Tidak ada riwayat dokumen penerimaan barang yang sesuai dengan kriteria filter yang Anda tentukan.
            </p>
        </div>
    <?php else: ?>
        <?php foreach ($penerimaanList as $idx => $doc): ?>
            <div class="bg-white rounded-2xl border <?= $doc['has_retur'] ? ($doc['has_pending'] ? 'border-amber-300 shadow-sm' : 'border-emerald-300 shadow-sm') : 'border-zcBrd shadow-sm' ?> overflow-hidden">
                <!-- Header Dokumen -->
                <div class="p-4 bg-slate-50 border-b border-zcBrd flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-white border border-zcBrd flex items-center justify-center text-xs font-black text-zcTxt">
                            <?= $idx + 1 ?>
                        </span>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-black text-zcTxt font-mono"><?= htmlspecialchars($doc['no_referensi']) ?></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $doc['sumber'] === 'PO' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-purple-50 text-purple-700 border border-purple-200' ?>">
                                    <?= $doc['sumber'] === 'PO' ? 'PO Distributor' : 'Kulakan' ?>
                                </span>
                                <?php if ($doc['has_retur']): ?>
                                    <?php if ($doc['has_pending']): ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300">
                                            Ada Retur Menunggu Pengganti (<?= $doc['total_rusak'] ?> Unit)
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            ✓ Klaim Retur Selesai (<?= $doc['total_rusak'] ?> Unit)
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Lengkap Sesuai (100%)
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="text-[11px] text-zcMut mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1">
                                <span>Tgl Terima: <strong class="text-slate-700"><?= date('d/m/Y', strtotime($doc['tanggal_terima'])) ?></strong></span>
                                <span>Distributor: <strong class="text-slate-700"><?= htmlspecialchars($doc['nama_supplier']) ?></strong></span>
                                <span>Petugas Penerima: <strong class="text-slate-700"><?= htmlspecialchars($doc['nama_petugas']) ?></strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="../inventori/cetak_nota_penerimaan.php?id=<?= $doc['id'] ?>" target="_blank" 
                           class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 border border-blue-200 text-blue-700 text-xs font-semibold rounded-xl hover:bg-blue-100 transition shadow-2xs"
                           title="Cetak Bukti Penerimaan Barang (A4)">
                            <svg class="w-3.5 h-3.5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                            <span>Bukti Penerimaan</span>
                        </a>
                        <?php if (!empty($doc['file_nota'])): ?>
                            <a href="../uploads/nota/<?= htmlspecialchars($doc['file_nota']) ?>" target="_blank" 
                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-zcBrd text-zc text-xs font-semibold rounded-xl hover:bg-slate-50 transition">
                                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                <span>Bukti Nota</span>
                            </a>
                        <?php endif; ?>
                        <a href="../inventori/cetak_batch_stiker.php?id_penerimaan=<?= $doc['id'] ?>" target="_blank" 
                           class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-zcBrd text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 transition">
                            <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                            <span>Stiker Barcode</span>
                        </a>
                    </div>
                </div>

                <?php if (!empty($doc['catatan'])): ?>
                    <div class="px-4 py-2 bg-amber-50/40 border-b border-amber-100 text-[11px] text-amber-900 italic">
                        Catatan: <?= htmlspecialchars($doc['catatan']) ?>
                    </div>
                <?php endif; ?>

                <!-- Rincian Item Dokumen -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/60 border-b border-zcBrd text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                <th class="py-2.5 px-4 w-10 text-center">No</th>
                                <th class="py-2.5 px-4">Produk & SKU</th>
                                <th class="py-2.5 px-4">Batch / Exp</th>
                                <th class="py-2.5 px-4 text-right">Dipesan</th>
                                <th class="py-2.5 px-4 text-right">Diterima Baik</th>
                                <th class="py-2.5 px-4 text-right">Retur / Rusak</th>
                                <th class="py-2.5 px-4 text-left">Satuan</th>
                                <th class="py-2.5 px-4 text-right">Harga Beli</th>
                                <th class="py-2.5 px-4 text-right">Subtotal</th>
                                <th class="py-2.5 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($doc['items'] as $itemIdx => $item): 
                                $subtotal = (float)$item['qty_terima'] * (float)$item['harga_beli'];
                                $isItemRetur = (int)$item['qty_rusak'] > 0;
                            ?>
                                <tr class="hover:bg-slate-50/60 <?= $isItemRetur ? 'bg-rose-50/20' : '' ?>">
                                    <td class="py-2.5 px-4 text-center font-mono text-zcMut"><?= $itemIdx + 1 ?></td>
                                    <td class="py-2.5 px-4">
                                        <div class="font-bold text-zcTxt"><?= htmlspecialchars($item['nama_produk']) ?></div>
                                        <div class="text-[10px] text-zcMut">
                                            <?= htmlspecialchars($item['nama_variasi']) ?> • <span class="font-mono"><?= htmlspecialchars($item['sku_variasi']) ?></span>
                                            • <span class="font-semibold text-slate-600"><?= htmlspecialchars($item['kategori']) ?></span>
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-4">
                                        <?php if ($item['no_batch']): ?>
                                            <div class="font-mono font-semibold text-slate-800"><?= htmlspecialchars($item['no_batch']) ?></div>
                                            <div class="text-[10px] text-zcMut">Exp: <?= $item['tgl_exp'] ? date('d/m/Y', strtotime($item['tgl_exp'])) : '-' ?></div>
                                        <?php else: ?>
                                            <span class="text-zcMut italic text-[11px]">Alkes (SN)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2.5 px-4 text-right font-mono font-semibold text-slate-700">
                                        <?= number_format($item['qty_pesan']) ?>
                                    </td>
                                    <td class="py-2.5 px-4 text-right font-mono font-bold text-emerald-600">
                                        <?= number_format($item['qty_terima']) ?>
                                    </td>
                                    <td class="py-2.5 px-4 text-right font-mono font-bold <?= $isItemRetur ? 'text-rose-600' : 'text-slate-400' ?>">
                                        <?= number_format($item['qty_rusak']) ?>
                                    </td>
                                    <td class="py-2.5 px-4 text-zcMut">
                                        <?= htmlspecialchars($item['satuan_nama']) ?>
                                    </td>
                                    <td class="py-2.5 px-4 text-right font-mono text-slate-600">
                                        Rp <?= number_format($item['harga_beli'], 0, ',', '.') ?>
                                    </td>
                                    <td class="py-2.5 px-4 text-right font-mono font-bold text-zcTxt">
                                        Rp <?= number_format($subtotal, 0, ',', '.') ?>
                                    </td>
                                    <td class="py-2.5 px-4 text-center">
                                        <?php if ($isItemRetur): 
                                            $stRetur = $item['status_retur'] ?? 'menunggu_pengganti';
                                        ?>
                                            <?php if ($stRetur === 'menunggu_pengganti'): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300" title="Menunggu penggantian fisik barang dari supplier">
                                                    Menunggu Pengganti (<?= $item['qty_rusak'] ?>)
                                                </span>
                                            <?php elseif ($stRetur === 'selesai_tukar_barang'): ?>
                                                <div class="inline-block text-left">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                        ✓ Diganti Unit Baru
                                                    </span>
                                                    <?php if (!empty($item['no_batch_pengganti'])): ?>
                                                        <div class="text-[9px] font-mono text-slate-700 mt-0.5">Batch: <?= htmlspecialchars($item['no_batch_pengganti']) ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($item['tgl_exp_pengganti'])): ?>
                                                        <div class="text-[9px] text-emerald-700">Exp: <?= date('d/m/Y', strtotime($item['tgl_exp_pengganti'])) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php elseif ($stRetur === 'selesai_potong_faktur'): ?>
                                                <div class="inline-block text-left">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-300">
                                                        ✓ Potong Tagihan
                                                    </span>
                                                    <?php if (!empty($item['catatan_retur'])): ?>
                                                        <div class="text-[9px] text-slate-600 italic mt-0.5 max-w-[120px] truncate" title="<?= htmlspecialchars($item['catatan_retur']) ?>"><?= htmlspecialchars($item['catatan_retur']) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                                Sesuai (100%)
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-slate-50 border-t border-zcBrd font-bold text-slate-700">
                            <tr>
                                <td colspan="3" class="py-2 px-4 text-right text-[11px] uppercase tracking-wider">Subtotal Dokumen:</td>
                                <td class="py-2 px-4 text-right font-mono"><?= number_format($doc['total_pesan']) ?></td>
                                <td class="py-2 px-4 text-right font-mono text-emerald-700"><?= number_format($doc['total_terima']) ?></td>
                                <td class="py-2 px-4 text-right font-mono <?= $doc['total_rusak'] > 0 ? 'text-rose-600' : 'text-slate-400' ?>"><?= number_format($doc['total_rusak']) ?></td>
                                <td></td>
                                <td></td>
                                <td class="py-2 px-4 text-right font-mono text-indigo-700 font-black">
                                    Rp <?= number_format($doc['total_nilai'], 0, ',', '.') ?>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
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
            LAPORAN PENERIMAAN BARANG &amp; MONITORING RETUR PARSIAL
        </h2>
        <div style="font-size: 8.5pt; color: #334155; margin-top: 4px;">
            Periode Laporan: <strong><?= date('d/m/Y', strtotime($dari)) ?></strong> s/d <strong><?= date('d/m/Y', strtotime($sampai)) ?></strong> 
            &nbsp;|&nbsp; Sumber: <strong><?= $sumber === 'semua' ? 'Semua Sumber' : htmlspecialchars($sumber) ?></strong>
            &nbsp;|&nbsp; Total Faktur: <strong><?= number_format($totalDokumen) ?> Dokumen</strong>
            &nbsp;|&nbsp; Total Fisik Diterima: <strong><?= number_format($totalQtyTerima) ?> Unit</strong>
        </div>
    </div>

    <!-- Ringkasan Statistik Cetak -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8pt;">
        <tr>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 20%;">
                <span style="color: #64748b; font-size: 7.5pt; display: block;">Total Dokumen Masuk:</span>
                <strong style="font-size: 9.5pt; color: #0f172a;"><?= number_format($totalDokumen) ?> faktur</strong>
            </td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 20%;">
                <span style="color: #64748b; font-size: 7.5pt; display: block;">Total Item Dipesan:</span>
                <strong style="font-size: 9.5pt; color: #1d4ed8;"><?= number_format($totalQtyPesan) ?> unit</strong>
            </td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 20%;">
                <span style="color: #64748b; font-size: 7.5pt; display: block;">Diterima Baik:</span>
                <strong style="font-size: 9.5pt; color: #047857;"><?= number_format($totalQtyTerima) ?> unit</strong>
            </td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 20%;">
                <span style="color: #64748b; font-size: 7.5pt; display: block;">Rusak / Retur Parsial:</span>
                <strong style="font-size: 9.5pt; color: #e11d48;"><?= number_format($totalQtyRusak) ?> unit</strong>
            </td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 20%;">
                <span style="color: #64748b; font-size: 7.5pt; display: block;">Nilai Pembelian Bersih:</span>
                <strong style="font-size: 9.5pt; color: #4338ca;">Rp <?= number_format($totalNilaiBeli, 0, ',', '.') ?></strong>
            </td>
        </tr>
    </table>

    <!-- Tabel Rincian Print -->
    <table class="print-table" style="width: 100%; border-collapse: collapse; font-size: 7.5pt; margin-bottom: 16px;">
        <thead>
            <tr style="background-color: #f1f5f9; color: #000000; -webkit-print-color-adjust: exact;">
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: center; width: 24px;">No</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: left; width: 85px;">No Faktur / Tgl</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: left;">Nama Produk &amp; Variasi</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: left; width: 85px;">Batch / Exp</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: left; width: 110px;">Distributor</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: right; width: 45px;">Pesan</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: right; width: 45px;">Terima</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: right; width: 45px;">Rusak</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: right; width: 75px;">Harga Beli</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: right; width: 85px;">Subtotal</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: center; width: 60px;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $noUrut = 1;
            foreach ($penerimaanList as $doc): 
                foreach ($doc['items'] as $itemIdx => $item):
                    $subtotal = (float)$item['qty_terima'] * (float)$item['harga_beli'];
                    $isFirst = ($itemIdx === 0);
                    $rowCount = count($doc['items']);
            ?>
                <tr>
                    <td style="border: 1px solid #94a3b8; padding: 4px; text-align: center;"><?= $noUrut++ ?></td>
                    <?php if ($isFirst): ?>
                        <td rowspan="<?= $rowCount ?>" style="border: 1px solid #94a3b8; padding: 4px; vertical-align: top; font-family: monospace;">
                            <strong><?= htmlspecialchars($doc['no_referensi']) ?></strong><br>
                            <span style="font-size: 7pt; font-family: sans-serif;"><?= date('d/m/Y', strtotime($doc['tanggal_terima'])) ?></span><br>
                            <span style="font-size: 7pt; font-weight: bold; color: #475569;"><?= $doc['sumber'] ?></span>
                        </td>
                    <?php endif; ?>
                    <td style="border: 1px solid #94a3b8; padding: 4px;">
                        <strong><?= htmlspecialchars($item['nama_produk']) ?></strong><br>
                        <span style="font-size: 7pt; color: #475569;"><?= htmlspecialchars($item['nama_variasi']) ?> (<?= htmlspecialchars($item['sku_variasi']) ?>)</span>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px; font-family: monospace;">
                        <?= $item['no_batch'] ? htmlspecialchars($item['no_batch']) : '-' ?><br>
                        <span style="font-size: 7pt; color: #475569;"><?= $item['tgl_exp'] ? date('d/m/Y', strtotime($item['tgl_exp'])) : 'Alkes' ?></span>
                    </td>
                    <?php if ($isFirst): ?>
                        <td rowspan="<?= $rowCount ?>" style="border: 1px solid #94a3b8; padding: 4px; vertical-align: top;">
                            <?= htmlspecialchars($doc['nama_supplier']) ?><br>
                            <span style="font-size: 7pt; color: #64748b;">Petugas: <?= htmlspecialchars($doc['nama_petugas']) ?></span>
                        </td>
                    <?php endif; ?>
                    <td style="border: 1px solid #94a3b8; padding: 4px; text-align: right; font-family: monospace;">
                        <?= number_format($item['qty_pesan']) ?>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px; text-align: right; font-family: monospace; font-weight: bold; color: #047857;">
                        <?= number_format($item['qty_terima']) ?>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px; text-align: right; font-family: monospace; <?= (int)$item['qty_rusak'] > 0 ? 'font-weight: bold; color: #e11d48;' : '' ?>">
                        <?= number_format($item['qty_rusak']) ?>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px; text-align: right; font-family: monospace;">
                        <?= number_format($item['harga_beli'], 0, ',', '.') ?>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px; text-align: right; font-family: monospace; font-weight: bold;">
                        <?= number_format($subtotal, 0, ',', '.') ?>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px; text-align: center; font-size: 7pt; font-weight: bold;">
                        <?php if ((int)$item['qty_rusak'] > 0): ?>
                            <?php if (($item['status_retur'] ?? '') === 'selesai_tukar_barang'): ?>
                                <span style="color: #047857;">SELESAI (GANTI)</span>
                            <?php elseif (($item['status_retur'] ?? '') === 'selesai_potong_faktur'): ?>
                                <span style="color: #1d4ed8;">SELESAI (POTONG)</span>
                            <?php else: ?>
                                <span style="color: #b45309;">MENUNGGU GANTI</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color: #047857;">LENGKAP</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php 
                endforeach; 
            endforeach; 
            ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold; color: #000000; -webkit-print-color-adjust: exact;">
                <td colspan="5" style="border: 1px solid #94a3b8; text-align: right; font-size: 8pt; padding: 5px 6px;">GRAND TOTAL:</td>
                <td style="border: 1px solid #94a3b8; text-align: right; font-family: monospace; padding: 5px 6px;"><?= number_format($totalQtyPesan) ?></td>
                <td style="border: 1px solid #94a3b8; text-align: right; font-family: monospace; padding: 5px 6px; color: #047857;"><?= number_format($totalQtyTerima) ?></td>
                <td style="border: 1px solid #94a3b8; text-align: right; font-family: monospace; padding: 5px 6px; color: #e11d48;"><?= number_format($totalQtyRusak) ?></td>
                <td style="border: 1px solid #94a3b8; padding: 5px 6px;"></td>
                <td style="border: 1px solid #94a3b8; text-align: right; font-family: monospace; font-size: 8.5pt; padding: 5px 6px; color: #4338ca;">
                    Rp <?= number_format($totalNilaiBeli, 0, ',', '.') ?>
                </td>
                <td style="border: 1px solid #94a3b8; padding: 5px 6px;"></td>
            </tr>
        </tfoot>
    </table>
</div>

<?php layoutFooter(); ?>
