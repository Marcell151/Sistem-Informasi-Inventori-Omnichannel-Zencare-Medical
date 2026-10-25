<?php
// File: laporan/mutasi_konversi.php
// Laporan Mutasi Stok Manual & Konversi Satuan UOM
// Superadmin Only — ZenCare Medical Omnichannel
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin']);

$tab        = $_GET['tab']        ?? 'konversi'; // konversi | mutasi
$dari       = $_GET['dari']       ?? date('Y-m-01', strtotime('-1 month'));
$sampai     = $_GET['sampai']     ?? date('Y-m-d');
$idVarSel   = intval($_GET['id_variasi'] ?? 0);
$searchQ    = trim($_GET['q'] ?? '');

// Master Dropdown Produk
$produkList = $pdo->query("
    SELECT pv.id, CONCAT(pi.nama_produk, ' — ', pv.nama_variasi) AS label, pv.sku_variasi 
    FROM produk_variasi pv 
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk 
    WHERE pv.is_active = 1 
    ORDER BY pi.nama_produk ASC
")->fetchAll(PDO::FETCH_ASSOC);

// =========================================================================
// TAB 1: KONVERSI SATUAN UOM (Bongkar Dus ke Eceran)
// =========================================================================
$konversiList = [];
$totalKonversiCount = 0;
$totalDusDibongkar = 0;
$totalEceranTerbentuk = 0;

if ($tab === 'konversi') {
    $paramsK = [$dari . ' 00:00:00', $sampai . ' 23:59:59'];
    $whereK = ["hk.created_at BETWEEN ? AND ?"];

    if ($idVarSel > 0) {
        $whereK[] = "hk.id_variasi = ?";
        $paramsK[] = $idVarSel;
    }

    if ($searchQ !== '') {
        $whereK[] = "(pi.nama_produk LIKE ? OR pv.nama_variasi LIKE ? OR pv.sku_variasi LIKE ? OR hk.batch_asal LIKE ? OR hk.batch_hasil LIKE ? OR u.nama_lengkap LIKE ?)";
        $kw = "%$searchQ%";
        for ($k = 0; $k < 6; $k++) $paramsK[] = $kw;
    }

    $whereClauseK = implode(" AND ", $whereK);

    $sqlKonversi = "
        SELECT 
            hk.*,
            COALESCE(u.nama_lengkap, 'Petugas Farmasi') AS nama_petugas,
            pi.nama_produk,
            pi.kategori,
            pv.nama_variasi,
            pv.sku_variasi,
            COALESCE(pv.satuan_besar, 'Dus') AS satuan_besar,
            COALESCE(pv.satuan_kecil, 'Pcs') AS satuan_kecil,
            pv.rasio_konversi
        FROM histori_konversi hk
        JOIN produk_variasi pv ON pv.id = hk.id_variasi
        JOIN produk_induk pi ON pi.id = pv.id_produk_induk
        LEFT JOIN users u ON u.id = hk.dibuat_oleh
        WHERE $whereClauseK
        ORDER BY hk.created_at DESC, hk.id DESC
    ";

    $stmtK = $pdo->prepare($sqlKonversi);
    $stmtK->execute($paramsK);
    $konversiList = $stmtK->fetchAll(PDO::FETCH_ASSOC);

    $totalKonversiCount = count($konversiList);
    foreach ($konversiList as $k) {
        $totalDusDibongkar += (int)$k['qty_box_buka'];
        $totalEceranTerbentuk += (int)$k['qty_pcs_hasil'];
    }
}

// =========================================================================
// TAB 2: MUTASI STOK MANUAL & PENYESUAIAN
// =========================================================================
$mutasiList = [];
$totalMutasiCount = 0;
$totalQtyMasuk = 0;
$totalQtyKeluar = 0;

if ($tab === 'mutasi') {
    $paramsM = [$dari . ' 00:00:00', $sampai . ' 23:59:59'];
    // Filter khusus: Mutasi Stok Manual & Opname fisik murni (Eksklusi pecahan konversi UOM agar tidak duplikasi dengan Tab Konversi)
    $whereM = [
        "ks.tanggal BETWEEN ? AND ?", 
        "ks.kanal = 'Manual'",
        "ks.keterangan NOT LIKE '%Bongkar%'",
        "ks.keterangan NOT LIKE '%Sub-Batch%'",
        "ks.keterangan NOT LIKE '%Konversi%'"
    ];

    if ($idVarSel > 0) {
        $whereM[] = "ks.id_variasi = ?";
        $paramsM[] = $idVarSel;
    }

    if ($searchQ !== '') {
        $whereM[] = "(pi.nama_produk LIKE ? OR pv.nama_variasi LIKE ? OR pv.sku_variasi LIKE ? OR ks.no_ref_dokumen LIKE ? OR ks.keterangan LIKE ? OR u.nama_lengkap LIKE ?)";
        $kw = "%$searchQ%";
        for ($k = 0; $k < 6; $k++) $paramsM[] = $kw;
    }

    $whereClauseM = implode(" AND ", $whereM);

    $sqlMutasi = "
        SELECT 
            ks.*,
            COALESCE(u.nama_lengkap, 'Petugas Gudang') AS nama_petugas,
            pi.nama_produk,
            pi.kategori,
            pv.nama_variasi,
            pv.sku_variasi,
            pv.satuan_besar,
            pv.satuan_kecil
        FROM kartu_stok ks
        JOIN produk_variasi pv ON pv.id = ks.id_variasi
        JOIN produk_induk pi ON pi.id = pv.id_produk_induk
        LEFT JOIN users u ON u.id = ks.dibuat_oleh
        WHERE $whereClauseM
        ORDER BY ks.tanggal DESC, ks.id DESC
    ";

    $stmtM = $pdo->prepare($sqlMutasi);
    $stmtM->execute($paramsM);
    $mutasiList = $stmtM->fetchAll(PDO::FETCH_ASSOC);

    $totalMutasiCount = count($mutasiList);
    foreach ($mutasiList as $m) {
        if ($m['jenis_mutasi'] === 'Masuk') {
            $totalQtyMasuk += (int)$m['qty'];
        } elseif ($m['jenis_mutasi'] === 'Keluar') {
            $totalQtyKeluar += (int)$m['qty'];
        }
    }
}

layoutHead('Laporan Mutasi & Konversi Stok');
layoutBodyOpen();
layoutSidebar('laporan_mutasi_konversi');
layoutHeader('Laporan Mutasi & Konversi Stok', 'Audit dekomposisi kemasan dus (Konversi UOM) dan riwayat penyesuaian stok fisik (Opname & Pemusnahan).');
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
<div class="print:hidden mb-5 p-4 rounded-xl border bg-indigo-50 border-indigo-200 text-indigo-900 text-xs flex gap-3 items-start">
    <svg class="w-5 h-5 shrink-0 mt-0.5 text-indigo-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 16V4m0 0L3 8m4-4 4 4M17 8v12m0 0 4-4m-4 4-4-4"/></svg>
    <div>
        <strong>Pemisahan Audit Mutasi vs Konversi</strong>:
        <ul class="list-disc pl-4 mt-1 space-y-0.5">
            <li><strong>Tab 1 (Konversi Satuan UOM)</strong>: Khusus mencatat riwayat pemecahan dus/box besar menjadi sub-batch eceran siap jual.</li>
            <li><strong>Tab 2 (Mutasi Manual &amp; Stok Opname)</strong>: Khusus mencatat penyesuaian fisik internal (Hasil Stok Opname, Pemusnahan Obat Rusak/Expired, Koreksi Selisih Fisik). Transaksi turunan konversi UOM otomatis dipisahkan agar data mutasi murni dan tidak tumpang tindih.</li>
        </ul>
    </div>
</div>

<!-- Tab Navigation (Screen Only) -->
<div class="print:hidden flex items-center gap-2 mb-6 border-b border-zcBrd pb-3">
    <a href="?tab=konversi&dari=<?= urlencode($dari) ?>&sampai=<?= urlencode($sampai) ?>&id_variasi=<?= $idVarSel ?>&q=<?= urlencode($searchQ) ?>" 
       class="px-4 py-2.5 text-xs font-bold rounded-xl transition flex items-center gap-2 <?= $tab === 'konversi' ? 'bg-zc text-white shadow-sm' : 'bg-white text-zcMut hover:text-zcTxt border border-zcBrd' ?>">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 4v16M17 4v16M3 8h4m10 0h4M3 16h4m10 0h4M7 12h10"/></svg>
        <span>Rekap Konversi Satuan UOM (Bongkar Dus)</span>
    </a>
    <a href="?tab=mutasi&dari=<?= urlencode($dari) ?>&sampai=<?= urlencode($sampai) ?>&id_variasi=<?= $idVarSel ?>&q=<?= urlencode($searchQ) ?>" 
       class="px-4 py-2.5 text-xs font-bold rounded-xl transition flex items-center gap-2 <?= $tab === 'mutasi' ? 'bg-zc text-white shadow-sm' : 'bg-white text-zcMut hover:text-zcTxt border border-zcBrd' ?>">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 16V4m0 0L3 8m4-4 4 4M17 8v12m0 0 4-4m-4 4-4-4"/></svg>
        <span>Rekap Mutasi Stok Manual &amp; Stok Opname (Non-Konversi)</span>
    </a>
</div>

<!-- Summary Stats Cards (Screen Only) -->
<?php if ($tab === 'konversi'): ?>
    <div class="print:hidden grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-zcBrd p-5 shadow-sm">
            <div class="text-xs font-bold text-zcMut mb-1">Total Aktivitas Konversi</div>
            <div class="text-2xl font-extrabold text-zcTxt"><?= number_format($totalKonversiCount) ?></div>
            <span class="text-[10px] text-zcMut">Proses Bongkar Dus Fisik</span>
        </div>
        <div class="bg-white rounded-2xl border border-zcBrd p-5 shadow-sm">
            <div class="text-xs font-bold text-amber-700 mb-1">Total Dus / Box Dibuka</div>
            <div class="text-2xl font-extrabold text-amber-600"><?= number_format($totalDusDibongkar) ?> <span class="text-xs font-semibold text-zcMut">Box</span></div>
            <span class="text-[10px] text-zcMut">Satuan Besar Berkurang</span>
        </div>
        <div class="bg-white rounded-2xl border border-zcBrd p-5 shadow-sm">
            <div class="text-xs font-bold text-emerald-700 mb-1">Total Eceran Terbentuk</div>
            <div class="text-2xl font-extrabold text-emerald-600">+<?= number_format($totalEceranTerbentuk) ?> <span class="text-xs font-semibold text-zcMut">Unit</span></div>
            <span class="text-[10px] text-zcMut">Sub-Batch Eceran Siap Jual</span>
        </div>
    </div>
<?php else: ?>
    <div class="print:hidden grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-zcBrd p-5 shadow-sm">
            <div class="text-xs font-bold text-zcMut mb-1">Total Mutasi Manual</div>
            <div class="text-2xl font-extrabold text-zcTxt"><?= number_format($totalMutasiCount) ?></div>
            <span class="text-[10px] text-zcMut">Koreksi & Pemusnahan</span>
        </div>
        <div class="bg-white rounded-2xl border border-zcBrd p-5 shadow-sm">
            <div class="text-xs font-bold text-emerald-700 mb-1">Total Qty Masuk (Opname Plus)</div>
            <div class="text-2xl font-extrabold text-emerald-600">+<?= number_format($totalQtyMasuk) ?> <span class="text-xs font-semibold text-zcMut">Unit</span></div>
            <span class="text-[10px] text-zcMut">Penyesuaian Fisik Bertambah</span>
        </div>
        <div class="bg-white rounded-2xl border border-zcBrd p-5 shadow-sm">
            <div class="text-xs font-bold text-rose-700 mb-1">Total Qty Keluar (Expired / Minus)</div>
            <div class="text-2xl font-extrabold text-rose-600">-<?= number_format($totalQtyKeluar) ?> <span class="text-xs font-semibold text-zcMut">Unit</span></div>
            <span class="text-[10px] text-rose-600 font-semibold">Pemusnahan & Retur Rusak</span>
        </div>
    </div>
<?php endif; ?>

<!-- Filter Form (Screen Only) -->
<form method="GET" class="print:hidden flex flex-wrap items-end gap-3 mb-6 p-5 bg-white border border-zcBrd rounded-2xl shadow-sm">
    <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
    <div>
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Dari Tanggal</label>
        <input type="date" name="dari" value="<?= htmlspecialchars($dari) ?>" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
    </div>
    <div>
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Sampai Tanggal</label>
        <input type="date" name="sampai" value="<?= htmlspecialchars($sampai) ?>" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
    </div>
    <div class="flex-1 min-w-[220px]">
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Produk Spesifik</label>
        <select name="id_variasi" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc w-full bg-white">
            <option value="0">Semua Produk</option>
            <?php foreach ($produkList as $pr): ?>
                <option value="<?= $pr['id'] ?>" <?= $idVarSel === (int)$pr['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($pr['label']) ?> (<?= htmlspecialchars($pr['sku_variasi']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-zcTxt mb-1.5">Pencarian Bebas</label>
        <input type="text" name="q" value="<?= htmlspecialchars($searchQ) ?>" placeholder="Batch, SKU, Petugas..." class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc min-w-[190px]">
    </div>
    <div class="flex items-center gap-2">
        <button type="submit" class="px-5 py-2.5 bg-zc hover:bg-zcHv text-white font-bold text-sm rounded-xl transition">
            Tampilkan
        </button>
        <?php if ($searchQ !== '' || $idVarSel > 0): ?>
            <a href="mutasi_konversi.php?tab=<?= $tab ?>" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-sm rounded-xl transition">
                Reset
            </a>
        <?php endif; ?>
        <button type="button" onclick="window.print()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition border border-slate-300 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Laporan
        </button>
    </div>
</form>

<!-- Main Table Card (Screen Only) -->
<div class="print:hidden bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden mb-6">
    <?php if ($tab === 'konversi'): ?>
        <!-- TABEL KONVERSI UOM -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-zcBrd text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                        <th class="py-3 px-4 w-10 text-center">No</th>
                        <th class="py-3 px-4">Waktu Eksekusi</th>
                        <th class="py-3 px-4">Petugas</th>
                        <th class="py-3 px-4">Produk Obat & SKU</th>
                        <th class="py-3 px-4">Batch Asal (Dus)</th>
                        <th class="py-3 px-4 text-right">Dus Dibongkar</th>
                        <th class="py-3 px-4 text-center">Rasio</th>
                        <th class="py-3 px-4">Sub-Batch Eceran</th>
                        <th class="py-3 px-4 text-right">Hasil Eceran</th>
                        <th class="py-3 px-4 text-right">Sisa Dus Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($konversiList)): ?>
                        <tr>
                            <td colspan="10" class="py-12 text-center text-zcMut">
                                Tidak ada data pembongkaran kemasan dus (Konversi UOM) pada rentang tanggal yang dipilih.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($konversiList as $idx => $k): ?>
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 text-center font-mono text-zcMut"><?= $idx + 1 ?></td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-zcTxt"><?= date('d/m/Y', strtotime($k['created_at'])) ?></div>
                                    <div class="text-[10px] text-zcMut font-mono"><?= date('H:i', strtotime($k['created_at'])) ?> WIB</div>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-700">
                                    <?= htmlspecialchars($k['nama_petugas']) ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-zcTxt"><?= htmlspecialchars($k['nama_produk']) ?></div>
                                    <div class="text-[10px] text-zcMut">
                                        <?= htmlspecialchars($k['nama_variasi']) ?> • <span class="font-mono"><?= htmlspecialchars($k['sku_variasi']) ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-mono font-semibold text-slate-800">
                                    <?= htmlspecialchars($k['batch_asal']) ?>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-amber-700">
                                    <?= number_format($k['qty_box_buka']) ?> <?= htmlspecialchars($k['satuan_besar']) ?>
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-[10px] text-slate-500">
                                    1 : <?= $k['rasio_konversi'] ?>
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-indigo-700">
                                    <span class="px-2 py-0.5 rounded bg-indigo-50 border border-indigo-200">
                                        <?= htmlspecialchars($k['batch_hasil']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600">
                                    +<?= number_format($k['qty_pcs_hasil']) ?> <?= htmlspecialchars($k['satuan_kecil']) ?>
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-600">
                                    <?= number_format($k['sisa_dus_akhir']) ?> <?= htmlspecialchars($k['satuan_besar']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($konversiList)): ?>
                    <tfoot class="bg-slate-50 border-t border-zcBrd font-bold text-slate-700">
                        <tr>
                            <td colspan="5" class="py-3 px-4 text-right uppercase text-[11px] tracking-wider">TOTAL KESELURUHAN:</td>
                            <td class="py-3 px-4 text-right font-mono text-amber-700 font-extrabold"><?= number_format($totalDusDibongkar) ?> Box</td>
                            <td></td>
                            <td></td>
                            <td class="py-3 px-4 text-right font-mono text-emerald-700 font-extrabold">+<?= number_format($totalEceranTerbentuk) ?> Unit</td>
                            <td></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    <?php else: ?>
        <!-- TABEL MUTASI STOK MANUAL -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-zcBrd text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                        <th class="py-3 px-4 w-10 text-center">No</th>
                        <th class="py-3 px-4">Waktu & Ref</th>
                        <th class="py-3 px-4">Petugas</th>
                        <th class="py-3 px-4">Produk & SKU</th>
                        <th class="py-3 px-4 text-center">Satuan</th>
                        <th class="py-3 px-4 text-center">Jenis</th>
                        <th class="py-3 px-4">Alasan Mutasi</th>
                        <th class="py-3 px-4 text-right">Kuantitas</th>
                        <th class="py-3 px-4 text-right">Sisa Stok</th>
                        <th class="py-3 px-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($mutasiList)): ?>
                        <tr>
                            <td colspan="10" class="py-12 text-center text-zcMut">
                                Tidak ada data mutasi manual pada rentang tanggal yang dipilih.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($mutasiList as $idx => $m): 
                            $isMasuk = ($m['jenis_mutasi'] === 'Masuk');
                        ?>
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 text-center font-mono text-zcMut"><?= $idx + 1 ?></td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-zcTxt"><?= date('d/m/Y', strtotime($m['tanggal'])) ?></div>
                                    <div class="text-[10px] text-zcMut font-mono"><?= date('H:i', strtotime($m['tanggal'])) ?> WIB</div>
                                    <?php if ($m['no_ref_dokumen']): ?>
                                        <div class="font-mono text-[9px] text-indigo-700 font-bold"><?= htmlspecialchars($m['no_ref_dokumen']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-700">
                                    <?= htmlspecialchars($m['nama_petugas']) ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-zcTxt"><?= htmlspecialchars($m['nama_produk']) ?></div>
                                    <div class="text-[10px] text-zcMut">
                                        <?= htmlspecialchars($m['nama_variasi']) ?> • <span class="font-mono"><?= htmlspecialchars($m['sku_variasi']) ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $m['satuan_tipe'] === 'besar' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-700' ?>">
                                        <?= ucfirst($m['satuan_tipe']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?php if ($isMasuk): ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Masuk (+)
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Keluar (-)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-800">
                                    <?= htmlspecialchars($m['alasan_mutasi']) ?>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold <?= $isMasuk ? 'text-emerald-600' : 'text-rose-600' ?>">
                                    <?= $isMasuk ? '+' : '-' ?><?= number_format($m['qty']) ?>
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-700">
                                    <?= number_format($m['sisa_stok']) ?>
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    <?= htmlspecialchars($m['keterangan'] ?: '-') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($mutasiList)): ?>
                    <tfoot class="bg-slate-50 border-t border-zcBrd font-bold text-slate-700">
                        <tr>
                            <td colspan="7" class="py-3 px-4 text-right uppercase text-[11px] tracking-wider">TOTAL MUTASI:</td>
                            <td class="py-3 px-4 text-right font-mono font-black text-slate-800">
                                <span class="text-emerald-700">+<?= number_format($totalQtyMasuk) ?></span> / 
                                <span class="text-rose-600">-<?= number_format($totalQtyKeluar) ?></span>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
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
            <?= $tab === 'konversi' ? 'LAPORAN REKAPITULASI KONVERSI SATUAN UOM (BONGKAR DUS)' : 'LAPORAN REKAPITULASI MUTASI STOK MANUAL &amp; STOK OPNAME' ?>
        </h2>
        <div style="font-size: 8.5pt; color: #334155; margin-top: 4px;">
            Periode: <strong><?= date('d/m/Y', strtotime($dari)) ?></strong> s/d <strong><?= date('d/m/Y', strtotime($sampai)) ?></strong>
            &nbsp;|&nbsp; Modul: <strong><?= $tab === 'konversi' ? 'Dekomposisi Kemasan Dus (UOM)' : 'Koreksi Stok Opname &amp; Fisik Gudang' ?></strong>
            &nbsp;|&nbsp; Total Catatan: <strong><?= number_format($tab === 'konversi' ? $totalKonversiCount : $totalMutasiCount) ?> Rekord</strong>
        </div>
    </div>

    <!-- Ringkasan Statistik Cetak -->
    <?php if ($tab === 'konversi'): ?>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8pt;">
            <tr>
                <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 33.3%;">
                    <span style="color: #64748b; font-size: 7.5pt; display: block;">Total Aktivitas Konversi:</span>
                    <strong style="font-size: 9.5pt; color: #0f172a;"><?= number_format($totalKonversiCount) ?> Transaksi</strong>
                </td>
                <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 33.3%;">
                    <span style="color: #64748b; font-size: 7.5pt; display: block;">Total Dus / Box Dibongkar:</span>
                    <strong style="font-size: 9.5pt; color: #b45309;"><?= number_format($totalDusDibongkar) ?> Box</strong>
                </td>
                <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 33.3%;">
                    <span style="color: #64748b; font-size: 7.5pt; display: block;">Total Eceran Dihasilkan:</span>
                    <strong style="font-size: 9.5pt; color: #047857;">+<?= number_format($totalEceranTerbentuk) ?> Unit Eceran</strong>
                </td>
            </tr>
        </table>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8pt;">
            <tr>
                <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 33.3%;">
                    <span style="color: #64748b; font-size: 7.5pt; display: block;">Total Mutasi Manual &amp; Opname:</span>
                    <strong style="font-size: 9.5pt; color: #0f172a;"><?= number_format($totalMutasiCount) ?> Penyesuaian</strong>
                </td>
                <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 33.3%;">
                    <span style="color: #64748b; font-size: 7.5pt; display: block;">Total Qty Masuk (Opname Plus):</span>
                    <strong style="font-size: 9.5pt; color: #047857;">+<?= number_format($totalQtyMasuk) ?> Unit Fisik</strong>
                </td>
                <td style="border: 1px solid #cbd5e1; padding: 5px 8px; background: #f8fafc; width: 33.3%;">
                    <span style="color: #64748b; font-size: 7.5pt; display: block;">Total Qty Keluar (Opname Minus / Exp):</span>
                    <strong style="font-size: 9.5pt; color: #e11d48;">-<?= number_format($totalQtyKeluar) ?> Unit Fisik</strong>
                </td>
            </tr>
        </table>
    <?php endif; ?>

    <?php if ($tab === 'konversi'): ?>
        <!-- Tabel Print Konversi -->
        <table class="print-table" style="width: 100%; border-collapse: collapse; font-size: 8.5pt; margin-bottom: 24px;">
            <thead>
                <tr style="background: #f1f5f9; color: #000000; -webkit-print-color-adjust: exact;">
                    <th style="text-align: center; width: 24px;">NO</th>
                    <th style="text-align: left; width: 85px;">WAKTU</th>
                    <th style="text-align: left; width: 95px;">PETUGAS</th>
                    <th style="text-align: left;">PRODUK & SKU</th>
                    <th style="text-align: left; width: 100px;">BATCH ASAL</th>
                    <th style="text-align: right; width: 65px;">QTY BUKA</th>
                    <th style="text-align: center; width: 45px;">RASIO</th>
                    <th style="text-align: left; width: 110px;">SUB-BATCH HASIL</th>
                    <th style="text-align: right; width: 75px;">QTY ECERAN</th>
                    <th style="text-align: right; width: 65px;">SISA DUS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($konversiList as $idx => $k): ?>
                    <tr>
                        <td style="text-align: center;"><?= $idx + 1 ?></td>
                        <td>
                            <?= date('d/m/Y', strtotime($k['created_at'])) ?><br>
                            <span style="font-size: 7.5pt;"><?= date('H:i', strtotime($k['created_at'])) ?> WIB</span>
                        </td>
                        <td><?= htmlspecialchars($k['nama_petugas']) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($k['nama_produk']) ?></strong><br>
                            <span style="font-size: 7.5pt;"><?= htmlspecialchars($k['nama_variasi']) ?> (<?= htmlspecialchars($k['sku_variasi']) ?>)</span>
                        </td>
                        <td style="font-family: monospace;"><?= htmlspecialchars($k['batch_asal']) ?></td>
                        <td style="text-align: right; font-family: monospace; font-weight: bold;">
                            <?= number_format($k['qty_box_buka']) ?> <?= htmlspecialchars($k['satuan_besar']) ?>
                        </td>
                        <td style="text-align: center; font-family: monospace; font-size: 7.5pt;">
                            1:<?= $k['rasio_konversi'] ?>
                        </td>
                        <td style="font-family: monospace; font-weight: bold;">
                            <?= htmlspecialchars($k['batch_hasil']) ?>
                        </td>
                        <td style="text-align: right; font-family: monospace; font-weight: bold;">
                            +<?= number_format($k['qty_pcs_hasil']) ?> <?= htmlspecialchars($k['satuan_kecil']) ?>
                        </td>
                        <td style="text-align: right; font-family: monospace;">
                            <?= number_format($k['sisa_dus_akhir']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background: #f1f5f9; font-weight: bold; color: #000000; -webkit-print-color-adjust: exact;">
                    <td colspan="5" style="text-align: right; font-size: 9pt;">TOTAL:</td>
                    <td style="text-align: right; font-family: monospace;"><?= number_format($totalDusDibongkar) ?> Box</td>
                    <td colspan="2"></td>
                    <td style="text-align: right; font-family: monospace;">+<?= number_format($totalEceranTerbentuk) ?> Unit</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

    <?php else: ?>
        <!-- Tabel Print Mutasi -->
        <table class="print-table" style="width: 100%; border-collapse: collapse; font-size: 8.5pt; margin-bottom: 24px;">
            <thead>
                <tr style="background: #f1f5f9; color: #000000; -webkit-print-color-adjust: exact;">
                    <th style="text-align: center; width: 24px;">NO</th>
                    <th style="text-align: left; width: 85px;">WAKTU</th>
                    <th style="text-align: left; width: 90px;">NO REF</th>
                    <th style="text-align: left;">PRODUK & SKU</th>
                    <th style="text-align: center; width: 45px;">SATUAN</th>
                    <th style="text-align: center; width: 50px;">JENIS</th>
                    <th style="text-align: left; width: 110px;">ALASAN</th>
                    <th style="text-align: right; width: 50px;">QTY</th>
                    <th style="text-align: right; width: 50px;">SISA</th>
                    <th style="text-align: left; width: 140px;">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mutasiList as $idx => $m): 
                    $isMasuk = ($m['jenis_mutasi'] === 'Masuk');
                ?>
                    <tr>
                        <td style="text-align: center;"><?= $idx + 1 ?></td>
                        <td>
                            <?= date('d/m/Y', strtotime($m['tanggal'])) ?><br>
                            <span style="font-size: 7.5pt;"><?= date('H:i', strtotime($m['tanggal'])) ?></span>
                        </td>
                        <td style="font-family: monospace; font-size: 7.5pt;">
                            <?= $m['no_ref_dokumen'] ? htmlspecialchars($m['no_ref_dokumen']) : '-' ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($m['nama_produk']) ?></strong><br>
                            <span style="font-size: 7.5pt;"><?= htmlspecialchars($m['nama_variasi']) ?></span>
                        </td>
                        <td style="text-align: center; font-size: 7.5pt;">
                            <?= ucfirst($m['satuan_tipe']) ?>
                        </td>
                        <td style="text-align: center; font-weight: bold; font-size: 7.5pt;">
                            <?= $m['jenis_mutasi'] ?>
                        </td>
                        <td><?= htmlspecialchars($m['alasan_mutasi']) ?></td>
                        <td style="text-align: right; font-family: monospace; font-weight: bold;">
                            <?= $isMasuk ? '+' : '-' ?><?= number_format($m['qty']) ?>
                        </td>
                        <td style="text-align: right; font-family: monospace;">
                            <?= number_format($m['sisa_stok']) ?>
                        </td>
                        <td style="font-size: 7.5pt;">
                            <?= htmlspecialchars($m['keterangan'] ?: '-') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background: #f1f5f9; font-weight: bold; color: #000000; -webkit-print-color-adjust: exact;">
                    <td colspan="7" style="text-align: right; font-size: 9pt;">TOTAL:</td>
                    <td style="text-align: right; font-family: monospace;">
                        +<?= number_format($totalQtyMasuk) ?> / -<?= number_format($totalQtyKeluar) ?>
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>

    <div style="margin-top: 15px; font-size: 7.5pt; color: #64748b; display: flex; justify-content: space-between;">
        <span>* Laporan ini dihasilkan secara otomatis oleh sistem komputer ZenCare Medical Omnichannel.</span>
        <span>Dokumen Resmi Sistem Inventori ZenCare Medical</span>
    </div>
</div>

<?php layoutFooter(); ?>
