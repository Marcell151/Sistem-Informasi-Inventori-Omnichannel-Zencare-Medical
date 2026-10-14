<?php
// File: laporan/penjualan.php - Laporan Volume Penjualan & Rincian Batch (Kuantitas Fisik Terjual)
// Superadmin Only — ZenCare Medical
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin']);

$dari   = $_GET['dari']    ?? date('Y-m-01');
$sampai = $_GET['sampai']  ?? date('Y-m-d');
$kanal  = $_GET['kanal']   ?? 'semua';  // semua / pos / ecommerce
$kategori = $_GET['kategori'] ?? 'semua'; // semua / Obat / Alat Kesehatan
$searchQ = trim($_GET['q'] ?? '');

$params = [$dari, $sampai];
$whereConditions = [
    "DATE(pj.created_at) BETWEEN ? AND ?",
    "pj.tipe_transaksi != 'Mutasi Konversi'",
    "pj.status_pesanan NOT IN ('Dibatalkan')"
];

if ($kanal === 'pos') {
    $whereConditions[] = "pj.tipe_transaksi = 'pos'";
} elseif ($kanal === 'ecommerce') {
    $whereConditions[] = "pj.tipe_transaksi = 'ecommerce'";
}

if ($kategori === 'Obat') {
    $whereConditions[] = "pi.kategori = 'Obat'";
} elseif ($kategori === 'Alat Kesehatan') {
    $whereConditions[] = "pi.kategori = 'Alat Kesehatan'";
}

if ($searchQ !== '') {
    $whereConditions[] = "(pi.nama_produk LIKE ? OR pv.nama_variasi LIKE ? OR pv.sku_variasi LIKE ? OR dp.catatan_logistik LIKE ? OR pj.no_invoice LIKE ?)";
    $params[] = "%$searchQ%";
    $params[] = "%$searchQ%";
    $params[] = "%$searchQ%";
    $params[] = "%$searchQ%";
    $params[] = "%$searchQ%";
}

$whereClause = implode(" AND ", $whereConditions);

// Fetch Rincian Item Penjualan Lengkap dengan Batch & Satuan Besar/Kecil
try {
    $sqlData = "
        SELECT
            pj.id AS id_penjualan,
            pj.no_invoice,
            pj.created_at,
            pj.tipe_transaksi,
            pj.status_pesanan,
            pi.nama_produk,
            pv.nama_variasi,
            pi.kategori,
            pv.sku_variasi,
            pv.satuan_besar,
            pv.satuan_kecil,
            pv.rasio_konversi,
            pv.harga_jual_besar,
            pv.harga_jual_kecil,
            dp.qty,
            dp.harga_satuan,
            dp.catatan_logistik
        FROM penjualan pj
        JOIN detail_penjualan dp ON pj.id = dp.id_penjualan
        JOIN produk_variasi pv ON dp.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        WHERE $whereClause
        ORDER BY pj.created_at DESC, dp.id DESC
    ";
    $stmtData = $pdo->prepare($sqlData);
    $stmtData->execute($params);
    $rawSales = $stmtData->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $rawSales = [];
}

// Olah Data: Identifikasi Satuan Besar (Grosir) vs Kecil (Eceran) & Hitung Ekuivalen
$salesRows = [];
$totalTransaksiSet = [];
$totalQtyFisikSemua = 0;
$totalQtyGrosirBox = 0;
$totalQtyEceranPcs = 0;
$totalPosItems = 0;
$totalOnlineItems = 0;

foreach ($rawSales as $row) {
    $totalTransaksiSet[$row['no_invoice']] = true;
    $rasio = max(1, intval($row['rasio_konversi'] ?: 1));
    $qty = intval($row['qty']);

    // Tentukan apakah transaksi menggunakan satuan besar (grosir) atau kecil (eceran)
    $isGrosir = ($row['tipe_transaksi'] === 'ecommerce') || (floatval($row['harga_satuan']) == floatval($row['harga_jual_besar']));
    
    if ($isGrosir) {
        $satuanLabel = $row['satuan_besar'] ?: 'Box';
        $tipeSatuan = 'Grosir (Besar)';
        $badgeSatuan = 'bg-amber-50 text-amber-800 border-amber-200';
        $ekuivalenKecil = $qty * $rasio;
        $totalQtyGrosirBox += $qty;
    } else {
        $satuanLabel = $row['satuan_kecil'] ?: 'Pcs';
        $tipeSatuan = 'Eceran (Kecil)';
        $badgeSatuan = 'bg-blue-50 text-blue-800 border-blue-200';
        $ekuivalenKecil = $qty;
        $totalQtyEceranPcs += $qty;
    }

    $totalQtyFisikSemua += $ekuivalenKecil;

    if ($row['tipe_transaksi'] === 'pos') {
        $totalPosItems += $ekuivalenKecil;
    } else {
        $totalOnlineItems += $ekuivalenKecil;
    }

    $row['is_grosir'] = $isGrosir;
    $row['satuan_label'] = $satuanLabel;
    $row['tipe_satuan'] = $tipeSatuan;
    $row['badge_satuan'] = $badgeSatuan;
    $row['ekuivalen_kecil'] = $ekuivalenKecil;
    $row['batch_info'] = !empty($row['catatan_logistik']) ? $row['catatan_logistik'] : '-';

    $salesRows[] = $row;
}

$summary = [
    'total_transaksi' => count($totalTransaksiSet),
    'total_fisik_semua' => $totalQtyFisikSemua,
    'total_grosir_box' => $totalQtyGrosirBox,
    'total_eceran_pcs' => $totalQtyEceranPcs,
    'item_pos' => $totalPosItems,
    'item_online' => $totalOnlineItems
];

// Pagination (Layar: 15 baris per halaman)
$perPage = 15;
$page = max(1, intval($_GET['page'] ?? 1));
$totalRecords = count($salesRows);
$totalPages = max(1, (int)ceil($totalRecords / $perPage));
if ($page > $totalPages) $page = $totalPages;
$salesPageList = array_slice($salesRows, ($page - 1) * $perPage, $perPage);

layoutHead('Laporan Penjualan Barang');
layoutBodyOpen();
layoutSidebar('laporan_penjualan');
layoutHeader('Laporan Penjualan Barang', 'Rekapitulasi kuantitas fisik barang terjual per transaksi, batch, dan satuan UOM (Omnichannel).');
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
    .print-table td > *, 
    .print-table th > * { 
        break-inside: avoid !important; 
        page-break-inside: avoid !important; 
    }
    .print-table th, .print-table td { border: 1px solid #94a3b8 !important; padding: 4.5px 6px !important; }
    .print-table th { background-color: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<!-- Informasi Batasan Sistem -->
<div class="print:hidden mb-5 p-4 rounded-xl border bg-blue-50 border-blue-200 text-blue-800 text-xs flex gap-3 items-start">
    <svg class="w-5 h-5 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
    <div>
        <strong>Mode Inventori Fisik & Pelacakan Batch Aktif</strong>: Laporan ini melacak kuantitas fisik barang keluar berdasarkan <strong>Nomor Batch / Sub-Batch</strong>, jenis kemasan (<strong>Grosir vs Eceran</strong>), dan kanal penjualan (POS / E-Commerce).
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
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Kategori</label>
        <select name="kategori" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc min-w-[140px]">
            <option value="semua" <?= $kategori==='semua'?'selected':'' ?>>Semua Kategori</option>
            <option value="Obat" <?= $kategori==='Obat'?'selected':'' ?>>Obat-obatan</option>
            <option value="Alat Kesehatan" <?= $kategori==='Alat Kesehatan'?'selected':'' ?>>Alat Kesehatan</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Kanal</label>
        <select name="kanal" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc min-w-[140px]">
            <option value="semua" <?= $kanal==='semua'?'selected':'' ?>>Semua Kanal</option>
            <option value="pos" <?= $kanal==='pos'?'selected':'' ?>>POS (Kasir Luring)</option>
            <option value="ecommerce" <?= $kanal==='ecommerce'?'selected':'' ?>>Online (E-Commerce)</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Cari Batch / SKU / Invoice</label>
        <input type="text" name="q" value="<?= htmlspecialchars($searchQ) ?>" placeholder="No. Batch, SKU, Invoice..." class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc min-w-[190px]">
    </div>
    <div class="flex-1 flex justify-end gap-2">
        <button type="submit" class="px-5 py-2.5 bg-zc hover:bg-zcHv text-white font-bold text-sm rounded-xl transition">
            Tampilkan
        </button>
        <?php if ($searchQ !== '' || $kanal !== 'semua' || $kategori !== 'semua'): ?>
            <a href="penjualan.php" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-sm rounded-xl transition">
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
<div class="print:hidden grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-zcBrd p-5 shadow-sm">
        <div class="text-xs font-bold text-zcMut mb-1">Total Transaksi Selesai</div>
        <div class="text-2xl font-extrabold text-zcTxt"><?= number_format($summary['total_transaksi']) ?></div>
        <span class="text-[10px] text-zcMut">Faktur POS &amp; Web</span>
    </div>
    <div class="bg-white rounded-2xl border border-zcBrd p-5 shadow-sm">
        <div class="text-xs font-bold text-zcMut mb-1">Total Fisik Terjual</div>
        <div class="text-2xl font-extrabold text-blue-600"><?= number_format($summary['total_fisik_semua']) ?> <span class="text-xs font-medium">unit fisik</span></div>
        <span class="text-[10px] text-zcMut">Ekuivalen Satuan Terkecil</span>
    </div>
    <div class="bg-white rounded-2xl border border-zcBrd p-5 shadow-sm">
        <div class="text-xs font-bold text-amber-700 mb-1">Terjual Grosir (Kemasan Besar)</div>
        <div class="text-2xl font-extrabold text-amber-600"><?= number_format($summary['total_grosir_box']) ?> <span class="text-xs font-medium">Box/Karton</span></div>
        <span class="text-[10px] text-amber-800">Kemasan Utuh Pabrik</span>
    </div>
    <div class="bg-white rounded-2xl border border-zcBrd p-5 shadow-sm">
        <div class="text-xs font-bold text-emerald-700 mb-1">Terjual Eceran (Kemasan Kecil)</div>
        <div class="text-2xl font-extrabold text-emerald-600"><?= number_format($summary['total_eceran_pcs']) ?> <span class="text-xs font-medium">Botol/Strip</span></div>
        <span class="text-[10px] text-emerald-800">Hasil Konversi Kasir Luring</span>
    </div>
</div>

<!-- Screen View: Paginated Table -->
<div class="print:hidden bg-white rounded-2xl border border-zcBrd shadow-sm overflow-hidden mb-8">
    <div class="px-5 py-4 border-b border-zcBrd flex items-center justify-between">
        <h3 class="text-sm font-bold text-zcTxt">Rincian Transaksi Penjualan Barang &amp; Pelacakan Batch</h3>
        <span class="text-xs text-zcMut">Total: <strong><?= $totalRecords ?></strong> baris transaksi</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead class="bg-slate-50 border-b border-zcBrd text-zcMut font-bold">
                <tr>
                    <th class="px-3.5 py-3 text-left">Tanggal &amp; Invoice</th>
                    <th class="px-3.5 py-3 text-left">Produk &amp; Variasi</th>
                    <th class="px-3.5 py-3 text-center">SKU</th>
                    <th class="px-3.5 py-3 text-left">No. Batch / Sub-Batch / SN</th>
                    <th class="px-3.5 py-3 text-center">Satuan Penjualan</th>
                    <th class="px-3.5 py-3 text-right">Qty Terjual</th>
                    <th class="px-3.5 py-3 text-right text-blue-600">Ekuivalen Fisik</th>
                    <th class="px-3.5 py-3 text-center">Kanal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zcBrd">
                <?php if (empty($salesRows)): ?>
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-zcMut italic">Tidak ada transaksi penjualan fisik pada periode dan filter ini.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($salesPageList as $s): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-3.5 py-3 whitespace-nowrap">
                            <div class="font-bold font-mono text-zcTxt"><?= htmlspecialchars($s['no_invoice']) ?></div>
                            <div class="text-[11px] text-zcMut"><?= date('d/m/Y H:i', strtotime($s['created_at'])) ?></div>
                        </td>
                        <td class="px-3.5 py-3">
                            <div class="font-bold text-zcTxt"><?= htmlspecialchars($s['nama_produk']) ?></div>
                            <div class="text-[11px] text-zcMut"><?= htmlspecialchars($s['nama_variasi']) ?></div>
                        </td>
                        <td class="px-3.5 py-3 text-center font-mono text-[11px] text-slate-600">
                            <?= htmlspecialchars($s['sku_variasi']) ?>
                        </td>
                        <td class="px-3.5 py-3 font-mono text-[11px]">
                            <?php if ($s['batch_info'] !== '-'): ?>
                                <div class="flex flex-wrap gap-1 max-w-[320px]">
                                    <?php 
                                    $batches = array_map('trim', explode(',', $s['batch_info']));
                                    foreach ($batches as $bt): 
                                        if (empty($bt)) continue;
                                    ?>
                                        <span class="inline-block px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-800 font-bold text-[11px] whitespace-normal">
                                            <?= htmlspecialchars($bt) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-slate-400 italic">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3.5 py-3 text-center whitespace-nowrap">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold border <?= $s['badge_satuan'] ?>">
                                <?= $s['tipe_satuan'] ?>
                            </span>
                        </td>
                        <td class="px-3.5 py-3 text-right font-extrabold text-zcTxt whitespace-nowrap">
                            <?= number_format($s['qty']) ?> <span class="font-normal text-[11px] text-zcMut"><?= htmlspecialchars($s['satuan_label']) ?></span>
                        </td>
                        <td class="px-3.5 py-3 text-right font-bold text-blue-600 bg-blue-50/20 whitespace-nowrap">
                            <?= number_format($s['ekuivalen_kecil']) ?> <span class="font-normal text-[10px] text-blue-500"><?= htmlspecialchars($s['satuan_kecil'] ?: 'Pcs') ?></span>
                        </td>
                        <td class="px-3.5 py-3 text-center whitespace-nowrap">
                            <?php if ($s['tipe_transaksi'] === 'pos'): ?>
                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold text-[10px] border border-emerald-200">POS Luring</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 font-bold text-[10px] border border-purple-200">E-Commerce</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls (Screen Only) -->
    <?php if ($totalRecords > 0): ?>
    <div class="px-5 py-3.5 bg-slate-50 border-t border-zcBrd flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        <span class="text-zcMut font-medium">
            Menampilkan <?= ($page - 1) * $perPage + 1 ?> – <?= min($page * $perPage, $totalRecords) ?> dari <?= $totalRecords ?> transaksi
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
</div>

<!-- ======================================================== -->
<!-- PRINT VIEW (A4 Portrait - Full Records Lengkap dengan Batch) -->
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
            LAPORAN REKAPITULASI PENJUALAN FISIK BARANG &amp; BATCH
        </h2>
        <div style="font-size: 8.5pt; color: #334155; margin-top: 4px;">
            Periode: <strong><?= date('d/m/Y', strtotime($dari)) ?></strong> s/d <strong><?= date('d/m/Y', strtotime($sampai)) ?></strong>
            &nbsp;|&nbsp; Kategori: <strong><?= htmlspecialchars($kategori === 'semua' ? 'Semua (Obat & Alkes)' : $kategori) ?></strong>
            &nbsp;|&nbsp; Kanal: <strong><?= htmlspecialchars(strtoupper($kanal)) ?></strong>
        </div>
    </div>

    <!-- Ringkasan Statistik Cetak -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8pt;">
        <tr>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 25%;">
                <span style="color: #64748b; font-size: 7.5pt; display: block;">Total Transaksi:</span>
                <strong style="font-size: 9.5pt; color: #0f172a;"><?= number_format($summary['total_transaksi']) ?> faktur</strong>
            </td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 25%;">
                <span style="color: #64748b; font-size: 7.5pt; display: block;">Total Fisik Terjual:</span>
                <strong style="font-size: 9.5pt; color: #1d4ed8;"><?= number_format($summary['total_fisik_semua']) ?> unit fisik</strong>
            </td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 25%;">
                <span style="color: #64748b; font-size: 7.5pt; display: block;">Terjual Grosir (Kemasan Besar):</span>
                <strong style="font-size: 9.5pt; color: #b45309;"><?= number_format($summary['total_grosir_box']) ?> Box/Karton</strong>
            </td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 25%;">
                <span style="color: #64748b; font-size: 7.5pt; display: block;">Terjual Eceran (Kemasan Kecil):</span>
                <strong style="font-size: 9.5pt; color: #047857;"><?= number_format($summary['total_eceran_pcs']) ?> Botol/Pcs</strong>
            </td>
        </tr>
    </table>

    <!-- Tabel Lengkap Cetak (Seluruh Transaksi dengan Kolom Batch & UOM) -->
    <table class="print-table" style="width: 100%; border-collapse: collapse; font-size: 7.5pt;">
        <thead>
            <tr style="background-color: #f1f5f9;">
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: center; width: 25px;">No</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: left; width: 85px;">Tanggal / Invoice</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: left;">Nama Produk &amp; Variasi</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: center; width: 65px;">SKU</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: left; width: 120px;">No. Batch / Sub-Batch / SN</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: center; width: 65px;">Satuan</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: right; width: 55px;">Qty</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: right; width: 65px;">Ekuivalen Fisik</th>
                <th style="border: 1px solid #94a3b8; padding: 4px; text-align: center; width: 55px;">Kanal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($salesRows)): ?>
            <tr>
                <td colspan="9" style="border: 1px solid #94a3b8; padding: 14px; text-align: center; color: #64748b; font-style: italic;">
                    Tidak ada transaksi penjualan fisik pada periode dan filter yang dipilih.
                </td>
            </tr>
            <?php else: ?>
                <?php foreach ($salesRows as $idx => $s): ?>
                <tr>
                    <td style="border: 1px solid #94a3b8; padding: 4px; text-align: center;"><?= $idx + 1 ?></td>
                    <td style="border: 1px solid #94a3b8; padding: 4px;">
                        <div style="font-weight: bold; font-family: monospace; font-size: 7pt;"><?= htmlspecialchars($s['no_invoice']) ?></div>
                        <div style="color: #64748b; font-size: 6.5pt;"><?= date('d/m/Y H:i', strtotime($s['created_at'])) ?></div>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px;">
                        <strong><?= htmlspecialchars($s['nama_produk']) ?></strong>
                        <div style="font-size: 7pt; color: #475569;"><?= htmlspecialchars($s['nama_variasi']) ?></div>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px; text-align: center; font-family: monospace; font-size: 7pt;">
                        <?= htmlspecialchars($s['sku_variasi']) ?>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px; font-family: monospace; font-size: 7pt;">
                        <?= htmlspecialchars($s['batch_info']) ?>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px; text-align: center; font-size: 7pt;">
                        <?= $s['is_grosir'] ? 'Grosir' : 'Eceran' ?>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px; text-align: right; font-weight: bold;">
                        <?= number_format($s['qty']) ?> <?= htmlspecialchars($s['satuan_label']) ?>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px; text-align: right; font-weight: bold; color: #1d4ed8;">
                        <?= number_format($s['ekuivalen_kecil']) ?> <?= htmlspecialchars($s['satuan_kecil'] ?: 'Pcs') ?>
                    </td>
                    <td style="border: 1px solid #94a3b8; padding: 4px; text-align: center; font-size: 7pt;">
                        <?= strtoupper($s['tipe_transaksi']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($salesRows)): ?>
        <tfoot>
            <tr style="background: #f8fafc; font-weight: bold;">
                <td colspan="6" style="border: 1px solid #94a3b8; padding: 5px; text-align: right;">TOTAL FISIK TERJUAL:</td>
                <td style="border: 1px solid #94a3b8; padding: 5px; text-align: right; color: #b45309;"><?= number_format($summary['total_grosir_box']) ?> Box / <?= number_format($summary['total_eceran_pcs']) ?> Ecer</td>
                <td style="border: 1px solid #94a3b8; padding: 5px; text-align: right; color: #1d4ed8;"><?= number_format($summary['total_fisik_semua']) ?> unit</td>
                <td style="border: 1px solid #94a3b8; padding: 5px; text-align: center;">-</td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>

    <!-- Footer Dokumen Resmi Cetak -->
    <div style="margin-top: 14px; display: flex; justify-content: space-between; font-size: 7.5pt; color: #64748b; border-top: 1px solid #cbd5e1; padding-top: 4px; page-break-inside: avoid;">
        <span>ZenCare Medical Inventory &bull; Laporan Penjualan Fisik &amp; Pelacakan Batch</span>
        <span>Dokumen Resmi Sistem Inventori ZenCare Medical</span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let dari = document.querySelector('input[name="dari"]');
    let sampai = document.querySelector('input[name="sampai"]');
    
    function validateDates() {
        if (dari && sampai) {
            sampai.min = dari.value;
            dari.max = sampai.value;
        }
    }

    if (dari && sampai) {
        dari.addEventListener('change', function() {
            if (sampai.value && sampai.value < dari.value) sampai.value = dari.value;
            validateDates();
        });
        
        sampai.addEventListener('change', function() {
            if (dari.value && sampai.value < dari.value) dari.value = sampai.value;
            validateDates();
        });
        
        validateDates();
    }
});
</script>

<?php layoutFooter(); ?>
