<?php
// File: laporan/logistik_medis.php
// Laporan Logistik Medis — Batch Obat & Jejak Serial Number Alkes
// Superadmin Only — ZenCare Medical v3.0
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin']);

$idCabang = 1;
$activeTab = $_GET['tab'] ?? 'batch';

// ─── FILTER ────────────────────────────────────────────────────────────────
$filterProduk = intval($_GET['id_variasi'] ?? 0);
$filterStatus = $_GET['status'] ?? '';

// ─── DATA: BATCH OBAT (Tab 1) ────────────────────────────────────────────
$sqlBatch = "
    SELECT sb.*, pv.sku_variasi, pi.nama_produk, pv.nama_variasi, pv.satuan_kecil,
           DATEDIFF(sb.tgl_exp, CURDATE()) AS hari_sisa,
           CASE
               WHEN sb.tgl_exp < CURDATE() THEN 'Kedaluwarsa'
               WHEN DATEDIFF(sb.tgl_exp, CURDATE()) <= 30 THEN 'Kritis (<30 hari)'
               WHEN DATEDIFF(sb.tgl_exp, CURDATE()) <= 90 THEN 'Perhatian (<90 hari)'
               ELSE 'Normal'
           END AS status_exp,
           CASE
               WHEN sb.tgl_exp < CURDATE() THEN 'bg-red-100 text-red-700'
               WHEN DATEDIFF(sb.tgl_exp, CURDATE()) <= 30 THEN 'bg-rose-100 text-rose-700'
               WHEN DATEDIFF(sb.tgl_exp, CURDATE()) <= 90 THEN 'bg-amber-100 text-amber-700'
               ELSE 'bg-emerald-100 text-emerald-700'
           END AS badge_cls
    FROM stok_batch sb
    JOIN produk_variasi pv ON pv.id = sb.id_variasi
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk
    WHERE sb.stok_sisa > 0
";
$paramsBatch = [];
if ($filterProduk) { $sqlBatch .= " AND sb.id_variasi = ?"; $paramsBatch[] = $filterProduk; }
$sqlBatch .= " ORDER BY sb.tgl_exp ASC, pi.nama_produk ASC";
$stmtBatch = $pdo->prepare($sqlBatch);
$stmtBatch->execute($paramsBatch);
$batchList = $stmtBatch->fetchAll();

// ─── DATA: JEJAK SN (Tab 2) ────────────────────────────────────────────
$sqlSN = "
    SELECT us.*, pi.nama_produk, pv.nama_variasi,
           pj.no_invoice, pj.tipe_transaksi
    FROM unit_serial us
    JOIN produk_variasi pv ON pv.id = us.id_variasi
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk
    LEFT JOIN penjualan pj ON pj.id = us.id_penjualan
    WHERE 1=1
";
$paramsSN = [];
if ($filterProduk) { $sqlSN .= " AND us.id_variasi = ?"; $paramsSN[] = $filterProduk; }
if ($filterStatus) { $sqlSN .= " AND us.status = ?"; $paramsSN[] = $filterStatus; }
$sqlSN .= " ORDER BY us.created_at DESC";
$stmtSN = $pdo->prepare($sqlSN);
$stmtSN->execute($paramsSN);
$snList = $stmtSN->fetchAll();

// ─── PRODUK OPTIONS ─────────────────────────────────────────────────────
$produkObat  = $pdo->query("SELECT pv.id, CONCAT(pi.nama_produk, ' — ', pv.nama_variasi) AS label FROM produk_variasi pv JOIN produk_induk pi ON pi.id = pv.id_produk_induk WHERE pi.kategori = 'Obat' AND pv.is_active = 1")->fetchAll();
$produkAlkes = $pdo->query("SELECT pv.id, CONCAT(pi.nama_produk, ' — ', pv.nama_variasi) AS label FROM produk_variasi pv JOIN produk_induk pi ON pi.id = pv.id_produk_induk WHERE pi.kategori = 'Alat Kesehatan' AND pv.is_active = 1")->fetchAll();

// ─── SUMMARY STATS ───────────────────────────────────────────────────────
$totalBatch    = count($batchList);
$batchKritis   = count(array_filter($batchList, fn($b) => in_array($b['status_exp'], ['Kritis (<30 hari)', 'Kedaluwarsa'])));
$totalSN       = count($snList);
$snTersedia    = count(array_filter($snList, fn($s) => $s['status'] === 'Tersedia'));
$snTerjual     = count(array_filter($snList, fn($s) => $s['status'] === 'Terjual'));
$snRetur       = count(array_filter($snList, fn($s) => $s['status'] === 'Retur/Rusak'));

layoutHead('Laporan Logistik Medis');
layoutBodyOpen();
layoutSidebar('laporan_logistik');
layoutHeader('Laporan Logistik Medis', 'Batch Obat Aktif & Jejak Serial Number Alat Kesehatan');
?>

<!-- Tab Navigation -->
<div class="flex gap-2 mb-6 bg-white border border-zcBrd rounded-2xl p-1.5 w-fit shadow-sm">
    <a href="?tab=batch" class="px-5 py-2 rounded-xl text-xs font-bold transition <?= $activeTab === 'batch' ? 'bg-zc text-white shadow-sm shadow-blue-500/20' : 'text-zcMut hover:text-zcTxt hover:bg-slate-50' ?>">
        💊 Batch Obat Aktif
    </a>
    <a href="?tab=sn" class="px-5 py-2 rounded-xl text-xs font-bold transition <?= $activeTab === 'sn' ? 'bg-zc text-white shadow-sm shadow-blue-500/20' : 'text-zcMut hover:text-zcTxt hover:bg-slate-50' ?>">
        🏷️ Jejak Serial Number (SN)
    </a>
</div>

<!-- TAB 1: BATCH OBAT -->
<?php if ($activeTab === 'batch'): ?>

<!-- Stats -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <?php
    $stats = [
        ['label'=>'Total Batch Aktif','val'=>$totalBatch,'cls'=>'bg-blue-50 border-blue-200 text-blue-700'],
        ['label'=>'Batch Kritis/Exp','val'=>$batchKritis,'cls'=>'bg-rose-50 border-rose-200 text-rose-700'],
    ];
    foreach ($stats as $s): ?>
    <div class="bg-white rounded-2xl border <?= $s['cls'] ?> p-4 shadow-sm">
        <div class="text-2xl font-extrabold mb-1"><?= $s['val'] ?></div>
        <div class="text-xs font-semibold"><?= $s['label'] ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filter -->
<form method="GET" class="flex flex-wrap gap-3 mb-5">
    <input type="hidden" name="tab" value="batch">
    <select name="id_variasi" class="text-xs border border-zcBrd rounded-xl px-3 py-2 focus:outline-none focus:border-zc bg-white min-w-[220px]">
        <option value="">Semua Produk Obat</option>
        <?php foreach ($produkObat as $p): ?>
        <option value="<?= $p['id'] ?>" <?= $filterProduk == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['label']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="px-4 py-2 bg-zc text-white rounded-xl text-xs font-bold hover:bg-zcHv">Filter</button>
    <a href="?tab=batch" class="px-4 py-2 border border-zcBrd rounded-xl text-xs font-semibold text-zcMut hover:bg-slate-50">Reset</a>
</form>

<!-- Batch Table -->
<div class="bg-white rounded-2xl border border-zcBrd shadow-sm overflow-hidden">
    <table class="w-full text-xs">
        <thead class="bg-slate-50 border-b border-zcBrd">
            <tr>
                <th class="text-left px-4 py-3 font-bold text-zcMut">Produk Obat</th>
                <th class="text-left px-4 py-3 font-bold text-zcMut">No. Batch</th>
                <th class="text-left px-4 py-3 font-bold text-zcMut">Tgl Exp</th>
                <th class="text-right px-4 py-3 font-bold text-zcMut">Stok Sisa</th>
                <th class="text-center px-4 py-3 font-bold text-zcMut">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zcBrd">
        <?php foreach ($batchList as $b): ?>
            <tr class="hover:bg-slate-50 transition <?= $b['status_exp'] === 'Kedaluwarsa' ? 'bg-red-50/50' : '' ?>">
                <td class="px-4 py-3">
                    <div class="font-bold text-zcTxt"><?= htmlspecialchars($b['nama_produk']) ?></div>
                    <div class="text-zcMut"><?= htmlspecialchars($b['sku_variasi']) ?></div>
                </td>
                <td class="px-4 py-3 font-mono font-bold text-zcTxt"><?= htmlspecialchars($b['no_batch']) ?></td>
                <td class="px-4 py-3">
                    <div class="font-bold text-zcTxt"><?= date('d M Y', strtotime($b['tgl_exp'])) ?></div>
                    <div class="text-zcMut"><?= $b['hari_sisa'] >= 0 ? $b['hari_sisa'] . ' hari lagi' : abs($b['hari_sisa']) . ' hari lalu' ?></div>
                </td>
                <td class="px-4 py-3 text-right font-bold text-zcTxt"><?= number_format($b['stok_sisa']) ?> <span class="font-normal text-zcMut"><?= $b['satuan_kecil'] ?></span></td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $b['badge_cls'] ?>"><?= $b['status_exp'] ?></span>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($batchList)): ?>
            <tr><td colspan="5" class="px-4 py-10 text-center text-zcMut">Tidak ada data batch aktif.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php endif; ?>

<!-- TAB 2: JEJAK SN -->
<?php if ($activeTab === 'sn'): ?>

<!-- Stats -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-blue-200 p-4 shadow-sm">
        <div class="text-2xl font-extrabold text-blue-700 mb-1"><?= $totalSN ?></div>
        <div class="text-xs font-semibold text-blue-700">Total SN Terdaftar</div>
    </div>
    <div class="bg-white rounded-2xl border border-emerald-200 p-4 shadow-sm">
        <div class="text-2xl font-extrabold text-emerald-700 mb-1"><?= $snTersedia ?></div>
        <div class="text-xs font-semibold text-emerald-700">Di Gudang (Tersedia)</div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <div class="text-2xl font-extrabold text-slate-700 mb-1"><?= $snTerjual ?></div>
        <div class="text-xs font-semibold text-slate-700">Terjual</div>
    </div>
    <div class="bg-white rounded-2xl border border-rose-200 p-4 shadow-sm">
        <div class="text-2xl font-extrabold text-rose-700 mb-1"><?= $snRetur ?></div>
        <div class="text-xs font-semibold text-rose-700">Retur / Rusak</div>
    </div>
</div>

<!-- Filter -->
<form method="GET" class="flex flex-wrap gap-3 mb-5">
    <input type="hidden" name="tab" value="sn">
    <select name="id_variasi" class="text-xs border border-zcBrd rounded-xl px-3 py-2 focus:outline-none focus:border-zc bg-white min-w-[220px]">
        <option value="">Semua Produk Alkes</option>
        <?php foreach ($produkAlkes as $p): ?>
        <option value="<?= $p['id'] ?>" <?= $filterProduk == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['label']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="status" class="text-xs border border-zcBrd rounded-xl px-3 py-2 focus:outline-none focus:border-zc bg-white">
        <option value="">Semua Status</option>
        <option value="Tersedia" <?= $filterStatus === 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
        <option value="Terjual" <?= $filterStatus === 'Terjual' ? 'selected' : '' ?>>Terjual</option>
        <option value="Retur/Rusak" <?= $filterStatus === 'Retur/Rusak' ? 'selected' : '' ?>>Retur / Rusak</option>
    </select>
    <button type="submit" class="px-4 py-2 bg-zc text-white rounded-xl text-xs font-bold hover:bg-zcHv">Filter</button>
    <a href="?tab=sn" class="px-4 py-2 border border-zcBrd rounded-xl text-xs font-semibold text-zcMut hover:bg-slate-50">Reset</a>
</form>

<!-- SN Table -->
<div class="bg-white rounded-2xl border border-zcBrd shadow-sm overflow-hidden">
    <table class="w-full text-xs">
        <thead class="bg-slate-50 border-b border-zcBrd">
            <tr>
                <th class="text-left px-4 py-3 font-bold text-zcMut">Serial Number</th>
                <th class="text-left px-4 py-3 font-bold text-zcMut">Produk Alkes</th>
                <th class="text-center px-4 py-3 font-bold text-zcMut">Status</th>
                <th class="text-left px-4 py-3 font-bold text-zcMut">Transaksi Terkait</th>
                <th class="text-left px-4 py-3 font-bold text-zcMut">Terdaftar</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zcBrd">
        <?php foreach ($snList as $sn): 
            $statusCls = match($sn['status']) {
                'Tersedia'    => 'bg-emerald-100 text-emerald-700',
                'Terjual'     => 'bg-slate-100 text-slate-700',
                'Retur/Rusak' => 'bg-rose-100 text-rose-700',
                default       => 'bg-slate-100 text-slate-600',
            };
        ?>
            <tr class="hover:bg-slate-50 transition">
                <td class="px-4 py-3 font-mono font-bold text-zcTxt"><?= htmlspecialchars($sn['serial_number']) ?></td>
                <td class="px-4 py-3">
                    <div class="font-semibold text-zcTxt"><?= htmlspecialchars($sn['nama_produk']) ?></div>
                    <div class="text-zcMut"><?= htmlspecialchars($sn['nama_variasi']) ?></div>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $statusCls ?>"><?= $sn['status'] ?></span>
                </td>
                <td class="px-4 py-3 text-zcMut">
                    <?php if ($sn['no_invoice']): ?>
                        <span class="font-mono text-[10px] bg-slate-100 px-2 py-0.5 rounded"><?= $sn['no_invoice'] ?></span>
                        <span class="ml-1 text-[10px] uppercase"><?= $sn['tipe_transaksi'] ?></span>
                    <?php elseif ($sn['status'] === 'Retur/Rusak'): ?>
                        <span class="text-rose-600"><?= htmlspecialchars($sn['catatan'] ?? 'Retur/Klaim Garansi') ?></span>
                    <?php else: ?>
                        <span class="text-slate-400">—</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-zcMut"><?= date('d M Y', strtotime($sn['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($snList)): ?>
            <tr><td colspan="5" class="px-4 py-10 text="center text-zcMut">Tidak ada data serial number.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php endif; ?>

<?php layoutFooter(); ?>
