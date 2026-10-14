<?php
// File: inventori/logistik_medis.php
// Modul Operasional Logistik Medis — Batch Obat & Pelacakan Serial Number (Garansi)
// Khusus Akses Operasional (Admin & Superadmin) — ZenCare Medical v3.0
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin', 'admin']);

// ─── HANDLER: TANDAI UNIT RUSAK / RETUR (KARANTINA) ─────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'karantina_sn') {
    $idSn = intval($_POST['id_sn'] ?? 0);
    $catatanKarantina = trim($_POST['catatan'] ?? 'Unit Rusak / Klaim Garansi');
    if ($idSn > 0) {
        $stmtCek = $pdo->prepare("SELECT serial_number FROM unit_serial WHERE id = ?");
        $stmtCek->execute([$idSn]);
        $snVal = $stmtCek->fetchColumn();
        if ($snVal) {
            $pdo->prepare("UPDATE unit_serial SET status = 'Retur/Rusak', catatan = ? WHERE id = ?")->execute([$catatanKarantina, $idSn]);
            $_SESSION['flash_msg'] = "Unit Serial Number '$snVal' berhasil diisolasi ke status Retur/Rusak (Karantina).";
            $redirectUrl = "logistik_medis.php?tab=sn";
            if (!empty($_GET['page_sn'])) $redirectUrl .= "&page_sn=" . intval($_GET['page_sn']);
            if (!empty($_GET['sn'])) $redirectUrl .= "&sn=" . urlencode($_GET['sn']);
            header("Location: " . $redirectUrl);
            exit;
        }
    }
}

$idCabang = 1;
$activeTab = $_GET['tab'] ?? 'batch';

// ─── FILTER ────────────────────────────────────────────────────────────────
$filterProduk    = intval($_GET['id_variasi'] ?? 0);
$filterStatus    = $_GET['status'] ?? '';
$filterSN        = trim($_GET['sn'] ?? '');
$filterStatusExp = $_GET['status_exp'] ?? '';
$filterTipeBatch = $_GET['tipe_batch'] ?? '';
$filterBatchQ    = trim($_GET['q_batch'] ?? '');

// ─── DATA: BATCH OBAT (Tab 1) ────────────────────────────────────────────
$sqlBatch = "
    SELECT sb.*, pv.sku_variasi, pi.nama_produk, pv.nama_variasi, pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, pi.batas_hari_expired,
           DATEDIFF(sb.tgl_exp, CURDATE()) AS hari_sisa,
           CASE
               WHEN sb.tgl_exp < CURDATE() THEN 'Kedaluwarsa'
               WHEN DATEDIFF(sb.tgl_exp, CURDATE()) <= COALESCE(pi.batas_hari_expired, 30) THEN CONCAT('Peringatan (≤ ', COALESCE(pi.batas_hari_expired, 30), ' hari)')
               ELSE 'Aman'
           END AS status_exp,
           CASE
               WHEN sb.tgl_exp < CURDATE() THEN 'bg-red-100 text-red-700'
               WHEN DATEDIFF(sb.tgl_exp, CURDATE()) <= COALESCE(pi.batas_hari_expired, 30) THEN 'bg-amber-100 text-amber-700'
               ELSE 'bg-emerald-100 text-emerald-700'
           END AS badge_cls
    FROM stok_batch sb
    JOIN produk_variasi pv ON pv.id = sb.id_variasi
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk
    WHERE sb.stok_sisa > 0
";
$paramsBatch = [];
if ($filterProduk) { $sqlBatch .= " AND sb.id_variasi = ?"; $paramsBatch[] = $filterProduk; }

if ($filterStatusExp === 'kedaluwarsa') {
    $sqlBatch .= " AND sb.tgl_exp < CURDATE()";
} elseif ($filterStatusExp === 'peringatan') {
    $sqlBatch .= " AND sb.tgl_exp >= CURDATE() AND DATEDIFF(sb.tgl_exp, CURDATE()) <= COALESCE(pi.batas_hari_expired, 30)";
} elseif ($filterStatusExp === 'aman') {
    $sqlBatch .= " AND DATEDIFF(sb.tgl_exp, CURDATE()) > COALESCE(pi.batas_hari_expired, 30)";
}

if ($filterTipeBatch === 'induk') {
    $sqlBatch .= " AND sb.no_batch NOT LIKE '%.%'";
} elseif ($filterTipeBatch === 'sub') {
    $sqlBatch .= " AND sb.no_batch LIKE '%.%'";
}

if ($filterBatchQ) {
    $sqlBatch .= " AND (sb.no_batch LIKE ? OR pi.nama_produk LIKE ? OR pv.nama_variasi LIKE ? OR pv.sku_variasi LIKE ?)";
    $kwB = "%$filterBatchQ%";
    $paramsBatch[] = $kwB;
    $paramsBatch[] = $kwB;
    $paramsBatch[] = $kwB;
    $paramsBatch[] = $kwB;
}

$sqlBatch .= " ORDER BY sb.tgl_exp ASC, pi.nama_produk ASC";
$stmtBatch = $pdo->prepare($sqlBatch);
$stmtBatch->execute($paramsBatch);
$batchList = $stmtBatch->fetchAll();

// ─── DATA: JEJAK SN (Tab 2) ────────────────────────────────────────────
$sqlSN = "
    SELECT us.*, pi.nama_produk, pv.nama_variasi, pv.sku_variasi,
           pj.no_invoice, pj.tipe_transaksi, pj.created_at AS tgl_terjual
    FROM unit_serial us
    JOIN produk_variasi pv ON pv.id = us.id_variasi
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk
    LEFT JOIN penjualan pj ON pj.id = us.id_penjualan
    WHERE 1=1
";
$paramsSN = [];
if ($filterProduk) { $sqlSN .= " AND us.id_variasi = ?"; $paramsSN[] = $filterProduk; }
if ($filterStatus) { $sqlSN .= " AND us.status = ?"; $paramsSN[] = $filterStatus; }
if ($filterSN) { 
    $sqlSN .= " AND (us.serial_number LIKE ? OR pi.nama_produk LIKE ? OR pv.nama_variasi LIKE ? OR pv.sku_variasi LIKE ?)"; 
    $kw = "%$filterSN%";
    $paramsSN[] = $kw; 
    $paramsSN[] = $kw; 
    $paramsSN[] = $kw; 
    $paramsSN[] = $kw; 
}
$sqlSN .= " ORDER BY us.status ASC, us.id DESC";
$stmtSN = $pdo->prepare($sqlSN);
$stmtSN->execute($paramsSN);
$snList = $stmtSN->fetchAll();

// ─── PRODUK OPTIONS ─────────────────────────────────────────────────────
$produkObat  = $pdo->query("SELECT pv.id, CONCAT(pi.nama_produk, ' — ', pv.nama_variasi) AS label FROM produk_variasi pv JOIN produk_induk pi ON pi.id = pv.id_produk_induk WHERE pi.kategori = 'Obat' AND pv.is_active = 1")->fetchAll();
$produkAlkes = $pdo->query("SELECT pv.id, CONCAT(pi.nama_produk, ' — ', pv.nama_variasi) AS label FROM produk_variasi pv JOIN produk_induk pi ON pi.id = pv.id_produk_induk WHERE pi.kategori = 'Alat Kesehatan' AND pv.is_active = 1")->fetchAll();

// ─── SUMMARY STATS ───────────────────────────────────────────────────────
$totalBatch    = count($batchList);
$batchAman     = count(array_filter($batchList, fn($b) => $b['status_exp'] === 'Aman'));
$batchKritis   = count(array_filter($batchList, fn($b) => str_contains($b['status_exp'], 'Peringatan')));
$batchExp      = count(array_filter($batchList, fn($b) => str_contains($b['status_exp'], 'Kedaluwarsa')));
$totalSN       = count($snList);
$snTersedia    = count(array_filter($snList, fn($s) => $s['status'] === 'Tersedia'));
$snTerjual     = count(array_filter($snList, fn($s) => $s['status'] === 'Terjual'));
$snRetur       = count(array_filter($snList, fn($s) => $s['status'] === 'Retur/Rusak'));

// ─── PAGINATION (15 items per page) ──────────────────────────────────────
$perPage = 15;
$pageBatch = max(1, intval($_GET['page'] ?? 1));
$totalPagesBatch = max(1, (int)ceil($totalBatch / $perPage));
if ($pageBatch > $totalPagesBatch) $pageBatch = $totalPagesBatch;
$batchPageList = array_slice($batchList, ($pageBatch - 1) * $perPage, $perPage);

$pageSN = max(1, intval($_GET['page_sn'] ?? 1));
$totalPagesSN = max(1, (int)ceil($totalSN / $perPage));
if ($pageSN > $totalPagesSN) $pageSN = $totalPagesSN;
$snPageList = array_slice($snList, ($pageSN - 1) * $perPage, $perPage);

layoutHead('Logistik Medis');
layoutBodyOpen();
layoutSidebar('logistik_medis_admin');
layoutHeader('Logistik Medis', 'Pemantauan status kedaluwarsa obat & pelacakan nomor seri / garansi alkes untuk operasional toko.');
?>

<!-- Tab Navigation (Tanpa Tombol Cetak Dokumen Laporan) -->
<div class="flex justify-between items-center mb-6">
    <div class="flex gap-2 bg-white border border-zcBrd rounded-2xl p-1.5 shadow-sm">
        <a href="?tab=batch" class="px-5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 <?= $activeTab === 'batch' ? 'bg-zc text-white shadow-sm shadow-blue-500/20' : 'text-zcMut hover:text-zcTxt hover:bg-slate-50' ?>">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/><path d="m8.5 8.5 7 7"/></svg>
            <span>Batch Obat Aktif</span>
        </a>
        <a href="?tab=sn" class="px-5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 <?= $activeTab === 'sn' ? 'bg-zc text-white shadow-sm shadow-blue-500/20' : 'text-zcMut hover:text-zcTxt hover:bg-slate-50' ?>">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6v12M7 6v12M11 6v12M14 6v12M17 6v12M20 6v12"/></svg>
            <span>Jejak Serial Number (SN)</span>
        </a>
    </div>
</div>

<!-- Flash Message -->
<?php if (!empty($_SESSION['flash_msg'])): ?>
    <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span><?= htmlspecialchars($_SESSION['flash_msg']) ?></span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
    </div>
    <?php unset($_SESSION['flash_msg']); ?>
<?php endif; ?>

<!-- TAB 1: BATCH OBAT -->
<?php if ($activeTab === 'batch'): ?>

<div>
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-blue-200 p-4 shadow-sm">
            <div class="text-2xl font-extrabold text-blue-700 mb-1"><?= $totalBatch ?></div>
            <div class="text-xs font-semibold text-blue-700">Total Batch Aktif</div>
        </div>
        <div class="bg-white rounded-2xl border border-emerald-200 p-4 shadow-sm">
            <div class="text-2xl font-extrabold text-emerald-700 mb-1"><?= $batchAman ?></div>
            <div class="text-xs font-semibold text-emerald-700">Batch Aman</div>
        </div>
        <div class="bg-white rounded-2xl border border-amber-200 p-4 shadow-sm">
            <div class="text-2xl font-extrabold text-amber-700 mb-1"><?= $batchKritis ?></div>
            <div class="text-xs font-semibold text-amber-700">Peringatan Expired</div>
        </div>
        <div class="bg-white rounded-2xl border border-rose-200 p-4 shadow-sm">
            <div class="text-2xl font-extrabold text-rose-700 mb-1"><?= $batchExp ?></div>
            <div class="text-xs font-semibold text-rose-700">Kedaluwarsa</div>
        </div>
    </div>

    <!-- Filter Tab 1 -->
    <form method="GET" class="flex flex-wrap items-center gap-3 mb-5">
        <input type="hidden" name="tab" value="batch">
        <div class="w-64">
            <select name="id_variasi" class="select2 w-full text-xs border border-zcBrd rounded-xl px-3 py-2 focus:outline-none focus:border-zc bg-white">
                <option value="">-- Semua Produk Obat --</option>
                <?php foreach ($produkObat as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $filterProduk == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <select name="status_exp" class="text-xs border border-zcBrd rounded-xl px-3 py-2 focus:outline-none focus:border-zc bg-white">
            <option value="">Semua Status Expired</option>
            <option value="aman" <?= $filterStatusExp === 'aman' ? 'selected' : '' ?>>Aman</option>
            <option value="peringatan" <?= $filterStatusExp === 'peringatan' ? 'selected' : '' ?>>Peringatan (≤ 30 hari)</option>
            <option value="kedaluwarsa" <?= $filterStatusExp === 'kedaluwarsa' ? 'selected' : '' ?>>Kedaluwarsa</option>
        </select>
        <select name="tipe_batch" class="text-xs border border-zcBrd rounded-xl px-3 py-2 focus:outline-none focus:border-zc bg-white">
            <option value="">Semua Tipe Batch</option>
            <option value="induk" <?= $filterTipeBatch === 'induk' ? 'selected' : '' ?>>Batch Induk (Satuan Besar)</option>
            <option value="sub" <?= $filterTipeBatch === 'sub' ? 'selected' : '' ?>>Sub-Batch (Satuan Kecil)</option>
        </select>
        <div class="relative">
            <input type="text" name="q_batch" value="<?= htmlspecialchars($filterBatchQ) ?>" placeholder="Cari No. Batch / Obat..." class="text-xs border border-zcBrd rounded-xl px-3 py-2 focus:outline-none focus:border-zc bg-white w-56">
        </div>
        <button type="submit" class="px-4 py-2 bg-zc text-white rounded-xl text-xs font-bold hover:bg-zcHv">Filter</button>
        <a href="?tab=batch" class="px-4 py-2 border border-zcBrd rounded-xl text-xs font-semibold text-zcMut hover:bg-slate-50">Reset</a>
    </form>

    <!-- Batch Table -->
    <div class="bg-white rounded-2xl border border-zcBrd shadow-sm overflow-hidden mb-6">
        <table class="w-full text-xs">
            <thead class="bg-slate-50 border-b border-zcBrd">
                <tr>
                    <th class="text-left px-4 py-3 font-bold text-zcMut">Produk Obat</th>
                    <th class="text-left px-4 py-3 font-bold text-zcMut">No. Batch</th>
                    <th class="text-left px-4 py-3 font-bold text-zcMut">Tgl Exp</th>
                    <th class="text-right px-4 py-3 font-bold text-zcMut">Stok Sisa</th>
                    <th class="text-center px-4 py-3 font-bold text-zcMut">Status</th>
                    <th class="text-center px-4 py-3 font-bold text-zcMut">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zcBrd">
            <?php foreach ($batchPageList as $b): ?>
                <tr class="hover:bg-slate-50 transition <?= $b['status_exp'] === 'Kedaluwarsa' ? 'bg-red-50/50' : '' ?>">
                    <td class="px-4 py-3">
                        <div class="font-bold text-zcTxt"><?= htmlspecialchars($b['nama_produk']) ?></div>
                        <div class="text-zcMut"><?= htmlspecialchars($b['sku_variasi']) ?></div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-mono font-bold text-zcTxt flex items-center gap-1.5">
                            <?= htmlspecialchars($b['no_batch']) ?>
                            <?php if (str_contains($b['no_batch'], '.')): ?>
                                <span class="text-[9px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">Sub-Batch (Satuan Kecil)</span>
                            <?php else: ?>
                                <span class="text-[9px] font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">Batch Induk (Satuan Besar)</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-bold text-zcTxt"><?= date('d M Y', strtotime($b['tgl_exp'])) ?></div>
                        <div class="text-zcMut"><?= $b['hari_sisa'] >= 0 ? $b['hari_sisa'] . ' hari lagi' : abs($b['hari_sisa']) . ' hari lalu' ?></div>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <?php if (str_contains($b['no_batch'], '.')): ?>
                            <div class="font-bold text-amber-800 text-sm"><?= number_format($b['stok_sisa']) ?> <?= htmlspecialchars($b['satuan_kecil']) ?></div>
                            <div class="text-[10px] text-amber-600 font-semibold">(Satuan Kecil / Eceran)</div>
                        <?php else: 
                            $rasio = max(1, (int)$b['rasio_konversi']);
                            $stokDus = floor($b['stok_sisa'] / $rasio);
                        ?>
                            <div class="font-bold text-blue-800 text-sm"><?= number_format($stokDus) ?> <?= htmlspecialchars($b['satuan_besar'] ?? 'Dus') ?></div>
                            <div class="text-[10px] text-blue-600 font-semibold">(Satuan Besar)</div>
                            <div class="text-[10px] text-zcMut font-normal mt-0.5">Total: <?= number_format($b['stok_sisa']) ?> <?= htmlspecialchars($b['satuan_kecil']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $b['badge_cls'] ?>"><?= $b['status_exp'] ?></span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button type="button" onclick="window.open('cetak_stiker.php?tipe=batch&id=<?= $b['id'] ?>', '_blank')" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 rounded-lg text-[10px] font-bold transition flex items-center gap-1.5 mx-auto">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                            Cetak
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($batchList)): ?>
                <tr><td colspan="6" class="px-4 py-10 text-center text-zcMut">Tidak ada data batch aktif.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination Tab 1 -->
        <?php if ($totalBatch > 0): ?>
        <div class="px-5 py-3.5 bg-slate-50 border-t border-zcBrd flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <span class="text-zcMut font-medium">
                Menampilkan <?= ($pageBatch - 1) * $perPage + 1 ?> – <?= min($pageBatch * $perPage, $totalBatch) ?> dari <?= $totalBatch ?> data batch
            </span>
            <div class="flex items-center gap-1.5 flex-wrap">
                <?php if ($pageBatch > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pageBatch - 1, 'tab' => 'batch'])) ?>" class="px-3 py-1.5 rounded-lg border border-zcBrd bg-white text-zcTxt hover:bg-slate-50 font-semibold transition">&larr; Sebelumnya</a>
                <?php else: ?>
                    <span class="px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-100 text-slate-400 font-semibold cursor-not-allowed">&larr; Sebelumnya</span>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $totalPagesBatch; $p++): ?>
                    <?php if ($p == 1 || $p == $totalPagesBatch || ($p >= $pageBatch - 2 && $p <= $pageBatch + 2)): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p, 'tab' => 'batch'])) ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border text-xs font-bold transition <?= $p == $pageBatch ? 'bg-zc border-zc text-white shadow-xs' : 'border-zcBrd bg-white text-zcTxt hover:bg-slate-50' ?>"><?= $p ?></a>
                    <?php elseif ($p == 2 || $p == $totalPagesBatch - 1): ?>
                        <span class="px-1 text-slate-400 font-bold">...</span>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pageBatch < $totalPagesBatch): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pageBatch + 1, 'tab' => 'batch'])) ?>" class="px-3 py-1.5 rounded-lg border border-zcBrd bg-white text-zcTxt hover:bg-slate-50 font-semibold transition">Selanjutnya &rarr;</a>
                <?php else: ?>
                    <span class="px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-100 text-slate-400 font-semibold cursor-not-allowed">Selanjutnya &rarr;</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- TAB 2: JEJAK SN -->
<?php elseif ($activeTab === 'sn'): ?>

<div>
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

    <!-- Filter Tab 2 -->
    <form method="GET" class="flex flex-wrap items-center gap-3 mb-5">
        <input type="hidden" name="tab" value="sn">
        <div class="w-64">
            <select name="id_variasi" class="select2 w-full text-xs border border-zcBrd rounded-xl px-3 py-2 focus:outline-none focus:border-zc bg-white">
                <option value="">-- Semua Produk Alkes --</option>
                <?php foreach ($produkAlkes as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $filterProduk == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <select name="status" class="text-xs border border-zcBrd rounded-xl px-3 py-2 focus:outline-none focus:border-zc bg-white">
            <option value="">Semua Status</option>
            <option value="Tersedia" <?= $filterStatus === 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
            <option value="Terjual" <?= $filterStatus === 'Terjual' ? 'selected' : '' ?>>Terjual</option>
            <option value="Retur/Rusak" <?= $filterStatus === 'Retur/Rusak' ? 'selected' : '' ?>>Retur / Rusak</option>
        </select>
        <div class="relative">
            <input type="text" name="sn" value="<?= htmlspecialchars($filterSN) ?>" placeholder="Cari SN atau Nama Produk..." class="text-xs border border-zcBrd rounded-xl px-3 py-2 focus:outline-none focus:border-zc bg-white w-60">
        </div>
        <button type="submit" class="px-4 py-2 bg-zc text-white rounded-xl text-xs font-bold hover:bg-zcHv">Filter</button>
        <a href="?tab=sn" class="px-4 py-2 border border-zcBrd rounded-xl text-xs font-semibold text-zcMut hover:bg-slate-50">Reset</a>
    </form>

    <!-- SN Table -->
    <div class="bg-white rounded-2xl border border-zcBrd shadow-sm overflow-hidden mb-6">
        <table class="w-full text-xs">
            <thead class="bg-slate-50 border-b border-zcBrd">
                <tr>
                    <th class="text-left px-4 py-3 font-bold text-zcMut">Serial Number</th>
                    <th class="text-left px-4 py-3 font-bold text-zcMut">Produk Alkes</th>
                    <th class="text-center px-4 py-3 font-bold text-zcMut">Status Unit</th>
                    <th class="text-left px-4 py-3 font-bold text-zcMut">Transaksi Terkait</th>
                    <th class="text-left px-4 py-3 font-bold text-zcMut">Terdaftar</th>
                    <th class="text-center px-4 py-3 font-bold text-zcMut">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y border-zcBrd">
            <?php foreach ($snPageList as $s): 
                $statusCls = match($s['status']) {
                    'Tersedia'    => 'bg-emerald-100 text-emerald-700',
                    'Terjual'     => 'bg-slate-100 text-slate-700',
                    'Retur/Rusak' => 'bg-rose-100 text-rose-700',
                    default       => 'bg-slate-100 text-slate-600',
                };
            ?>
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 font-mono font-bold text-zcTxt"><?= htmlspecialchars($s['serial_number']) ?></td>
                    <td class="px-4 py-3">
                        <div class="font-semibold text-zcTxt"><?= htmlspecialchars($s['nama_produk']) ?></div>
                        <div class="text-zcMut"><?= htmlspecialchars($s['nama_variasi']) ?></div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $statusCls ?>"><?= $s['status'] ?></span>
                    </td>
                    <td class="px-4 py-3 text-zcMut">
                        <?php if ($s['no_invoice']): ?>
                            <div class="font-mono text-[11px] font-bold text-slate-800">
                                <a href="../admin/pesanan_detail.php?id=<?= $s['no_invoice'] ?>" class="text-blue-600 hover:underline"><?= htmlspecialchars($s['no_invoice']) ?></a>
                                <span class="text-[9px] font-sans px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 uppercase font-semibold"><?= htmlspecialchars($s['tipe_transaksi'] ?? 'POS') ?></span>
                            </div>
                            <?php if (!empty($s['tgl_terjual'])): ?>
                                <div class="text-[10px] text-zcMut mt-0.5">
                                    Terjual: <?= date('d M Y H:i', strtotime($s['tgl_terjual'])) ?>
                                    <?php
                                    $tglBeli = strtotime($s['tgl_terjual']);
                                    $sisaGaransi = 30 - floor((time() - $tglBeli) / 86400);
                                    if ($sisaGaransi >= 0) {
                                        echo '<span class="text-emerald-700 font-semibold ml-1">&bull; Garansi: Aktif (' . $sisaGaransi . ' hr)</span>';
                                    } else {
                                        echo '<span class="text-slate-400 font-medium ml-1">&bull; Garansi: Habis</span>';
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                        <?php elseif ($s['status'] === 'Retur/Rusak'): ?>
                            <span class="text-rose-600 font-semibold text-xs"><?= htmlspecialchars($s['catatan'] ?? 'Unit Rusak / Karantina') ?></span>
                        <?php else: ?>
                            <span class="text-slate-400 italic">Belum Ada Transaksi (Siap Jual)</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-zcMut text-[11px]"><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <button type="button" onclick="window.open('cetak_stiker.php?tipe=sn&id=<?= $s['serial_number'] ?>', '_blank')" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 rounded-lg text-[10px] font-bold transition flex items-center gap-1" title="Cetak Stiker Barcode">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                <span>Stiker</span>
                            </button>
                            <?php if ($s['status'] === 'Terjual'): ?>
                            <button type="button" onclick="openKarantinaModal(<?= $s['id'] ?>, '<?= htmlspecialchars($s['serial_number']) ?>', '<?= htmlspecialchars(addslashes($s['nama_produk'])) ?>')" class="px-2 py-1.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 rounded-lg text-[10px] font-bold transition flex items-center gap-1" title="Klaim retur / garansi alkes yang sudah dibeli pelanggan">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                <span>Retur Barang</span>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($snList)): ?>
                <tr><td colspan="6" class="px-4 py-10 text-center text-zcMut">Tidak ada jejak serial number.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination Tab 2 -->
        <?php if ($totalSN > 0): ?>
        <div class="px-5 py-3.5 bg-slate-50 border-t border-zcBrd flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <span class="text-zcMut font-medium">
                Menampilkan <?= ($pageSN - 1) * $perPage + 1 ?> – <?= min($pageSN * $perPage, $totalSN) ?> dari <?= $totalSN ?> unit SN
            </span>
            <div class="flex items-center gap-1.5 flex-wrap">
                <?php if ($pageSN > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page_sn' => $pageSN - 1, 'tab' => 'sn'])) ?>" class="px-3 py-1.5 rounded-lg border border-zcBrd bg-white text-zcTxt hover:bg-slate-50 font-semibold transition">&larr; Sebelumnya</a>
                <?php else: ?>
                    <span class="px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-100 text-slate-400 font-semibold cursor-not-allowed">&larr; Sebelumnya</span>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $totalPagesSN; $p++): ?>
                    <?php if ($p == 1 || $p == $totalPagesSN || ($p >= $pageSN - 2 && $p <= $pageSN + 2)): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page_sn' => $p, 'tab' => 'sn'])) ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border text-xs font-bold transition <?= $p == $pageSN ? 'bg-zc border-zc text-white shadow-xs' : 'border-zcBrd bg-white text-zcTxt hover:bg-slate-50' ?>"><?= $p ?></a>
                    <?php elseif ($p == 2 || $p == $totalPagesSN - 1): ?>
                        <span class="px-1 text-slate-400 font-bold">...</span>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pageSN < $totalPagesSN): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page_sn' => $pageSN + 1, 'tab' => 'sn'])) ?>" class="px-3 py-1.5 rounded-lg border border-zcBrd bg-white text-zcTxt hover:bg-slate-50 font-semibold transition">Selanjutnya &rarr;</a>
                <?php else: ?>
                    <span class="px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-100 text-slate-400 font-semibold cursor-not-allowed">Selanjutnya &rarr;</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>

<!-- MODAL POPUP KARANTINA UNIT SN -->
<div id="modalKarantina" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-zcBrd p-6 relative animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center gap-3 text-rose-600 mb-4">
            <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center shrink-0 border border-rose-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div>
                <h3 class="font-bold text-base text-slate-800">Isolasi Unit ke Karantina</h3>
                <p class="text-xs text-slate-500">Tandai unit ini sebagai rusak / klaim garansi toko.</p>
            </div>
        </div>

        <form method="POST" action="">
            <input type="hidden" name="action" value="karantina_sn">
            <input type="hidden" name="id_sn" id="modal_id_sn" value="">

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 mb-4 text-xs">
                <div class="text-slate-500">Serial Number:</div>
                <div class="font-mono font-bold text-slate-800 text-sm mt-0.5" id="modal_sn_text">-</div>
                <div class="text-slate-500 mt-2">Produk:</div>
                <div class="font-semibold text-slate-800" id="modal_produk_text">-</div>
            </div>

            <div class="mb-5">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Alasan Karantina / Kerusakan:</label>
                <textarea name="catatan" rows="3" required placeholder="Contoh: Layar mati, sensor error, klaim garansi retur pelanggan, dll." class="w-full text-xs border border-zcBrd rounded-xl p-3 focus:outline-none focus:border-rose-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeKarantinaModal()" class="px-4 py-2 border border-zcBrd rounded-xl text-xs font-semibold text-zcMut hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-xs">Konfirmasi Isolasi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openKarantinaModal(id, sn, produk) {
    document.getElementById('modal_id_sn').value = id;
    document.getElementById('modal_sn_text').innerText = sn;
    document.getElementById('modal_produk_text').innerText = produk;
    document.getElementById('modalKarantina').classList.remove('hidden');
}
function closeKarantinaModal() {
    document.getElementById('modalKarantina').classList.add('hidden');
}
</script>

<?php
layoutFooter();
?>
