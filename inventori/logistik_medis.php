<?php
// File: inventori/logistik_medis.php
// Modul Operasional Logistik Medis — Batch Obat & Pelacakan Serial Number (Garansi)
// Khusus Akses Operasional (Admin & Superadmin) — ZenCare Medical v3.0
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin', 'admin']);

// ─── HANDLER: TANDAI UNIT RUSAK / RETUR (KARANTINA) ─────────────────────────
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'karantina_sn') {
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
               WHEN sb.tgl_exp < CURDATE() THEN 'bg-red-100 text-red-700 border border-red-200'
               WHEN DATEDIFF(sb.tgl_exp, CURDATE()) <= COALESCE(pi.batas_hari_expired, 30) THEN 'bg-amber-100 text-amber-700 border border-amber-200'
               ELSE 'bg-emerald-100 text-emerald-700 border border-emerald-200'
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

$sqlBatch .= " ORDER BY sb.tgl_exp ASC, sb.id ASC";
$stmtBatch = $pdo->prepare($sqlBatch);
$stmtBatch->execute($paramsBatch);
$batchList = $stmtBatch->fetchAll(PDO::FETCH_ASSOC);

// Pengelompokan Batch per Produk Variasi
$groupedBatchList = [];
foreach ($batchList as $b) {
    $vid = $b['id_variasi'];
    if (!isset($groupedBatchList[$vid])) {
        $groupedBatchList[$vid] = [
            'id_variasi'     => $vid,
            'nama_produk'    => $b['nama_produk'],
            'nama_variasi'   => $b['nama_variasi'],
            'sku_variasi'    => $b['sku_variasi'],
            'satuan_kecil'   => $b['satuan_kecil'],
            'satuan_besar'   => $b['satuan_besar'],
            'rasio_konversi' => $b['rasio_konversi'],
            'min_exp'        => $b['tgl_exp'],
            'min_hari_sisa'  => $b['hari_sisa'],
            'total_stok'     => 0,
            'batches'        => []
        ];
    }
    $groupedBatchList[$vid]['total_stok'] += $b['stok_sisa'];
    if ($b['tgl_exp'] < $groupedBatchList[$vid]['min_exp']) {
        $groupedBatchList[$vid]['min_exp'] = $b['tgl_exp'];
        $groupedBatchList[$vid]['min_hari_sisa'] = $b['hari_sisa'];
    }
    $groupedBatchList[$vid]['batches'][] = $b;
}

// Urutkan Produk: Produk yang paling mendekati masa kadaluarsa muncul paling atas (FEFO Produk)
uasort($groupedBatchList, function ($a, $b) {
    if ($a['min_exp'] !== $b['min_exp']) {
        return strcmp($a['min_exp'], $b['min_exp']);
    }
    return strcasecmp($a['nama_produk'], $b['nama_produk']);
});

// ─── DATA: JEJAK SN (Tab 2) ────────────────────────────────────────────
// Helper Ranking Prioritas Unit SN (Aturan 4-Tier Ketat):
// 1. Terjual dengan Garansi Aktif paling mendekati mau habis (sisa hari paling sedikit: 0, 1, 2, ... 30)
// 2. Unit Retur/Rusak (Karantina)
// 3. Terjual dengan Garansi Habis (sisa hari <= 0)
// 4. Produk Tersedia / Belum Terjual (Urutan barang terdaftar paling lama ke paling baru)
if (!function_exists('getSNUnitScore')) {
    function getSNUnitScore($s) {
        $status = $s['status'];
        if ($status === 'Terjual') {
            $tglBeli = !empty($s['tgl_terjual']) ? strtotime($s['tgl_terjual']) : null;
            $sisaGaransi = $tglBeli ? (30 - floor((time() - $tglBeli) / 86400)) : -999;
            if ($sisaGaransi >= 0) {
                // Tier 1: Garansi aktif, paling sedikit sisa hari paling atas
                return [1, (int)$sisaGaransi, $s['id']];
            } else {
                // Tier 3: Garansi habis
                return [3, (int)abs($sisaGaransi), $s['id']];
            }
        } elseif ($status === 'Retur/Rusak') {
            // Tier 2: Retur / Rusak
            return [2, 0, $s['id']];
        } else {
            // Tier 4: Tersedia (belum terjual) diurutkan urutan masuk: terdaftar paling lama ke paling baru (created_at ASC)
            $createdTs = !empty($s['created_at']) ? strtotime($s['created_at']) : 0;
            return [4, $createdTs, $s['id']];
        }
    }
}

if (!function_exists('compareSNUnits')) {
    function compareSNUnits($a, $b) {
        $scoreA = getSNUnitScore($a);
        $scoreB = getSNUnitScore($b);
        if ($scoreA[0] !== $scoreB[0]) return $scoreA[0] <=> $scoreB[0];
        if ($scoreA[1] !== $scoreB[1]) return $scoreA[1] <=> $scoreB[1];
        return $scoreA[2] <=> $scoreB[2];
    }
}

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

$stmtSN = $pdo->prepare($sqlSN);
$stmtSN->execute($paramsSN);
$snList = $stmtSN->fetchAll(PDO::FETCH_ASSOC);

// Pengelompokan SN per Produk Variasi
$groupedSNList = [];
foreach ($snList as $s) {
    $vid = $s['id_variasi'];
    if (!isset($groupedSNList[$vid])) {
        $groupedSNList[$vid] = [
            'id_variasi'     => $vid,
            'nama_produk'    => $s['nama_produk'],
            'nama_variasi'   => $s['nama_variasi'],
            'sku_variasi'    => $s['sku_variasi'],
            'count_total'    => 0,
            'count_tersedia' => 0,
            'count_terjual'  => 0,
            'count_retur'    => 0,
            'min_score'      => [99, 999999999, 0],
            'units'          => []
        ];
    }
    $groupedSNList[$vid]['count_total']++;
    if ($s['status'] === 'Tersedia') $groupedSNList[$vid]['count_tersedia']++;
    elseif ($s['status'] === 'Terjual') $groupedSNList[$vid]['count_terjual']++;
    elseif ($s['status'] === 'Retur/Rusak') $groupedSNList[$vid]['count_retur']++;
    
    $score = getSNUnitScore($s);
    if ($score[0] < $groupedSNList[$vid]['min_score'][0] || 
       ($score[0] === $groupedSNList[$vid]['min_score'][0] && $score[1] < $groupedSNList[$vid]['min_score'][1])) {
        $groupedSNList[$vid]['min_score'] = $score;
    }
    
    $groupedSNList[$vid]['units'][] = $s;
}

// Urutkan unit di dalam setiap produk sesuai aturan 4 tier
foreach ($groupedSNList as &$grp) {
    usort($grp['units'], 'compareSNUnits');
}
unset($grp);

// Urutkan kelompok produk alkes berdasarkan unit paling prioritas (garansi aktif hampir habis duluan)
uasort($groupedSNList, function($a, $b) {
    $scA = $a['min_score'];
    $scB = $b['min_score'];
    if ($scA[0] !== $scB[0]) return $scA[0] <=> $scB[0];
    if ($scA[1] !== $scB[1]) return $scA[1] <=> $scB[1];
    return strcasecmp($a['nama_produk'], $b['nama_produk']);
});

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

// ─── PAGINATION PER PRODUK (8 Produk per halaman) ─────────────────────────
$perPage = 8;

$totalGroupsBatch = count($groupedBatchList);
$pageBatch = max(1, intval($_GET['page'] ?? 1));
$totalPagesBatch = max(1, (int)ceil($totalGroupsBatch / $perPage));
if ($pageBatch > $totalPagesBatch) $pageBatch = $totalPagesBatch;
$pagedGroupsBatch = array_slice($groupedBatchList, ($pageBatch - 1) * $perPage, $perPage, true);

$totalGroupsSN = count($groupedSNList);
$pageSN = max(1, intval($_GET['page_sn'] ?? 1));
$totalPagesSN = max(1, (int)ceil($totalGroupsSN / $perPage));
if ($pageSN > $totalPagesSN) $pageSN = $totalPagesSN;
$pagedGroupsSN = array_slice($groupedSNList, ($pageSN - 1) * $perPage, $perPage, true);

layoutHead('Logistik Medis');
layoutBodyOpen();
layoutSidebar('logistik_medis_admin');
layoutHeader('Logistik Medis', 'Pemantauan status kedaluwarsa obat & pelacakan nomor seri / garansi alkes per produk.');
?>

<!-- Tab Navigation -->
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
            <div class="text-xs font-semibold text-blue-700">Total Batch Aktif (<?= $totalGroupsBatch ?> Produk)</div>
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

    <!-- Toolbar Pengaturan Tampilan Accordion & Seleksi Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 bg-white p-3.5 rounded-2xl border border-zcBrd shadow-2xs">
        <div class="flex items-center gap-3 flex-wrap">
            <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer select-none">
                <input type="checkbox" id="check_all_page" onchange="toggleSelectAllPage(this.checked)" class="rounded text-zc focus:ring-zc h-4 w-4">
                <span>Pilih Semua di Halaman Ini</span>
            </label>
            <div class="h-4 w-px bg-slate-200 hidden sm:block"></div>
            <div class="flex items-center gap-1.5 text-xs">
                <button type="button" onclick="expandAllGroups()" class="px-2.5 py-1 text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-semibold transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    <span>Buka Semua</span>
                </button>
                <button type="button" onclick="collapseAllGroups()" class="px-2.5 py-1 text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-semibold transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"></polyline></svg>
                    <span>Tutup Semua</span>
                </button>
            </div>
        </div>
    </div>

    <!-- DAFTAR PRODUK (ACCORDION PER PRODUK OBAT) -->
    <?php if (empty($pagedGroupsBatch)): ?>
        <div class="bg-white rounded-2xl border border-zcBrd p-10 text-center text-zcMut">
            Tidak ada data batch obat aktif yang ditemukan sesuai filter.
        </div>
    <?php else: ?>
        <div class="space-y-4 mb-6">
            <?php foreach ($pagedGroupsBatch as $vid => $grp): 
                $minExpDate = date('d M Y', strtotime($grp['min_exp']));
                $minSisa = $grp['min_hari_sisa'];
                $expLabel = $minSisa >= 0 ? "Exp Terdekat: $minExpDate ($minSisa hr lagi)" : "Exp Terdekat: $minExpDate (Kedaluwarsa!)";
                $expBadgeCls = $minSisa < 0 ? 'bg-red-50 text-red-700 border-red-200' : ($minSisa <= 30 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200');
            ?>
            <div class="group-accordion bg-white rounded-2xl border border-zcBrd shadow-xs overflow-hidden transition-all duration-200" id="group_card_<?= $vid ?>">
                <!-- Accordion Header -->
                <div class="p-4 bg-slate-50/90 hover:bg-slate-100/80 transition cursor-pointer flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-zcBrd" onclick="toggleAccordion('<?= $vid ?>')">
                    <div class="flex items-center gap-3">
                        <div onclick="event.stopPropagation()">
                            <input type="checkbox" class="group-select-all rounded text-zc focus:ring-zc h-4 w-4 cursor-pointer" onchange="toggleGroupBatches(this, '<?= $vid ?>')" title="Pilih semua batch produk ini">
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($grp['nama_produk']) ?></span>
                                <span class="text-xs text-slate-500 font-medium">— <?= htmlspecialchars($grp['nama_variasi']) ?></span>
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-200 text-slate-700 font-bold"><?= htmlspecialchars($grp['sku_variasi']) ?></span>
                            </div>
                            <div class="text-xs text-slate-500 flex items-center gap-3 mt-1 flex-wrap">
                                <span>Total: <b><?= count($grp['batches']) ?></b> Batch</span>
                                <span>&bull;</span>
                                <span>Total Stok: <b><?= number_format($grp['total_stok']) ?></b> <?= htmlspecialchars($grp['satuan_kecil'] ?: 'Pcs') ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 self-end md:self-auto" onclick="event.stopPropagation()">
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full border <?= $expBadgeCls ?>">
                            <?= $expLabel ?>
                        </span>
                        <button type="button" onclick="toggleAccordion('<?= $vid ?>')" class="w-8 h-8 rounded-xl bg-white border border-zcBrd hover:bg-slate-100 flex items-center justify-center text-slate-600 transition shadow-2xs" title="Buka / Tutup">
                            <svg class="accordion-icon w-4 h-4 transition-transform duration-200 rotate-180" id="group_icon_<?= $vid ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                    </div>
                </div>

                <!-- Accordion Body: Tabel Batch (Default Terbuka) -->
                <div class="accordion-body divide-y divide-zcBrd" id="group_body_<?= $vid ?>">
                    <table class="w-full text-xs">
                        <thead class="bg-white border-b border-zcBrd text-zcMut">
                            <tr>
                                <th class="w-10 text-center px-3 py-2.5">Pilih</th>
                                <th class="text-left px-4 py-2.5 font-bold">No. Batch</th>
                                <th class="text-left px-4 py-2.5 font-bold">Tgl Kadaluarsa (Exp)</th>
                                <th class="text-right px-4 py-2.5 font-bold">Stok Sisa</th>
                                <th class="text-center px-4 py-2.5 font-bold">Status</th>
                                <th class="text-center px-4 py-2.5 font-bold">Aksi & Kuantitas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zcBrd bg-white">
                            <?php foreach ($grp['batches'] as $b): ?>
                            <tr class="hover:bg-slate-50/70 transition <?= $b['status_exp'] === 'Kedaluwarsa' ? 'bg-red-50/40' : '' ?>">
                                <td class="text-center px-3 py-3">
                                    <input type="checkbox" class="batch-item-chk rounded text-zc focus:ring-zc h-4 w-4 cursor-pointer" 
                                           data-type="batch"
                                           data-id="<?= $b['id'] ?>"
                                           data-name="<?= htmlspecialchars($b['nama_produk'] . ' - ' . $b['nama_variasi']) ?>"
                                           data-code="<?= htmlspecialchars($b['no_batch']) ?>"
                                           data-exp="<?= date('d/m/Y', strtotime($b['tgl_exp'])) ?>"
                                           onchange="toggleBatchItem(this)">
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
                                    <div class="text-zcMut text-[11px]"><?= $b['hari_sisa'] >= 0 ? $b['hari_sisa'] . ' hari lagi' : abs($b['hari_sisa']) . ' hari lalu' ?></div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <?php if (str_contains($b['no_batch'], '.')): ?>
                                        <div class="font-bold text-amber-800 text-xs"><?= number_format($b['stok_sisa']) ?> <?= htmlspecialchars($b['satuan_kecil']) ?></div>
                                        <div class="text-[10px] text-amber-600 font-semibold">(Satuan Kecil / Eceran)</div>
                                    <?php else: 
                                        $rasio = max(1, (int)$b['rasio_konversi']);
                                        $stokDus = floor($b['stok_sisa'] / $rasio);
                                    ?>
                                        <div class="font-bold text-blue-800 text-xs"><?= number_format($stokDus) ?> <?= htmlspecialchars($b['satuan_besar'] ?? 'Dus') ?></div>
                                        <div class="text-[10px] text-blue-600 font-semibold">(Satuan Besar)</div>
                                        <div class="text-[10px] text-zcMut font-normal mt-0.5">Total: <?= number_format($b['stok_sisa']) ?> <?= htmlspecialchars($b['satuan_kecil']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $b['badge_cls'] ?>"><?= $b['status_exp'] ?></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <div class="flex items-center gap-1 text-[11px] font-semibold text-slate-500 bg-slate-50 px-1.5 py-0.5 rounded-lg border border-slate-200">
                                            <span>Jumlah:</span>
                                            <input type="number" min="1" max="100" value="1" class="batch-item-qty w-10 text-center text-xs font-bold border border-slate-300 rounded bg-white py-0.5 focus:outline-none focus:border-zc" onchange="updateBatchQty(this, <?= $b['id'] ?>)">
                                        </div>
                                        <button type="button" onclick="printSingleBatch(this, <?= $b['id'] ?>)" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 rounded-lg text-[10px] font-bold transition flex items-center gap-1 shadow-2xs" title="Cetak batch ini langsung">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                            Cetak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination Tab 1 (Per Kelompok Produk) -->
        <?php if ($totalGroupsBatch > 0): ?>
        <div class="px-5 py-3.5 bg-slate-50 rounded-2xl border border-zcBrd flex flex-col sm:flex-row items-center justify-between gap-3 text-xs mb-8">
            <span class="text-zcMut font-medium">
                Menampilkan <?= ($pageBatch - 1) * $perPage + 1 ?> – <?= min($pageBatch * $perPage, $totalGroupsBatch) ?> dari <?= $totalGroupsBatch ?> kelompok produk obat (Total <?= $totalBatch ?> batch)
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
    <?php endif; ?>
</div>

<!-- TAB 2: JEJAK SERIAL NUMBER ALKES -->
<?php elseif ($activeTab === 'sn'): ?>

<div>
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-blue-200 p-4 shadow-sm">
            <div class="text-2xl font-extrabold text-blue-700 mb-1"><?= $totalSN ?></div>
            <div class="text-xs font-semibold text-blue-700">Total SN (<?= $totalGroupsSN ?> Produk)</div>
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
            <option value="">Semua Status Unit</option>
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

    <!-- Toolbar Pengaturan Tampilan Accordion & Seleksi Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 bg-white p-3.5 rounded-2xl border border-zcBrd shadow-2xs">
        <div class="flex items-center gap-3 flex-wrap">
            <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer select-none">
                <input type="checkbox" id="check_all_page" onchange="toggleSelectAllPage(this.checked)" class="rounded text-zc focus:ring-zc h-4 w-4">
                <span>Pilih Semua di Halaman Ini</span>
            </label>
            <div class="h-4 w-px bg-slate-200 hidden sm:block"></div>
            <div class="flex items-center gap-1.5 text-xs">
                <button type="button" onclick="expandAllGroups()" class="px-2.5 py-1 text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-semibold transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    <span>Buka Semua</span>
                </button>
                <button type="button" onclick="collapseAllGroups()" class="px-2.5 py-1 text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-semibold transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"></polyline></svg>
                    <span>Tutup Semua</span>
                </button>
            </div>
        </div>
    </div>

    <!-- DAFTAR PRODUK (ACCORDION PER PRODUK ALKES) -->
    <?php if (empty($pagedGroupsSN)): ?>
        <div class="bg-white rounded-2xl border border-zcBrd p-10 text-center text-zcMut">
            Tidak ada jejak Serial Number alat kesehatan yang ditemukan sesuai filter.
        </div>
    <?php else: ?>
        <div class="space-y-4 mb-6">
            <?php foreach ($pagedGroupsSN as $vid => $grp): ?>
            <div class="group-accordion bg-white rounded-2xl border border-zcBrd shadow-xs overflow-hidden transition-all duration-200" id="group_card_<?= $vid ?>">
                <!-- Accordion Header -->
                <div class="p-4 bg-slate-50/90 hover:bg-slate-100/80 transition cursor-pointer flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-zcBrd" onclick="toggleAccordion('<?= $vid ?>')">
                    <div class="flex items-center gap-3">
                        <div onclick="event.stopPropagation()">
                            <input type="checkbox" class="group-select-all rounded text-zc focus:ring-zc h-4 w-4 cursor-pointer" onchange="toggleGroupSN(this, '<?= $vid ?>')" title="Pilih semua SN produk ini">
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($grp['nama_produk']) ?></span>
                                <span class="text-xs text-slate-500 font-medium">— <?= htmlspecialchars($grp['nama_variasi']) ?></span>
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-200 text-slate-700 font-bold"><?= htmlspecialchars($grp['sku_variasi']) ?></span>
                            </div>
                            <div class="text-xs text-slate-500 flex items-center gap-3 mt-1 flex-wrap">
                                <span>Total: <b><?= $grp['count_total'] ?></b> Unit</span>
                                <span>&bull;</span>
                                <span class="text-emerald-700 font-semibold">Tersedia: <?= $grp['count_tersedia'] ?></span>
                                <span>&bull;</span>
                                <span class="text-slate-600 font-semibold">Terjual: <?= $grp['count_terjual'] ?></span>
                                <?php if ($grp['count_retur'] > 0): ?>
                                <span>&bull;</span>
                                <span class="text-rose-600 font-bold">Retur/Rusak: <?= $grp['count_retur'] ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 self-end md:self-auto" onclick="event.stopPropagation()">
                        <button type="button" onclick="toggleAccordion('<?= $vid ?>')" class="w-8 h-8 rounded-xl bg-white border border-zcBrd hover:bg-slate-100 flex items-center justify-center text-slate-600 transition shadow-2xs" title="Buka / Tutup">
                            <svg class="accordion-icon w-4 h-4 transition-transform duration-200 rotate-180" id="group_icon_<?= $vid ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                    </div>
                </div>

                <!-- Accordion Body: Tabel Jejak SN (Default Terbuka) -->
                <div class="accordion-body divide-y divide-zcBrd" id="group_body_<?= $vid ?>">
                    <table class="w-full text-xs">
                        <thead class="bg-white border-b border-zcBrd text-zcMut">
                            <tr>
                                <th class="w-10 text-center px-3 py-2.5">Pilih</th>
                                <th class="text-left px-4 py-2.5 font-bold">Serial Number (SN)</th>
                                <th class="text-center px-4 py-2.5 font-bold">Status Unit</th>
                                <th class="text-left px-4 py-2.5 font-bold">Status Transaksi & Garansi Toko</th>
                                <th class="text-left px-4 py-2.5 font-bold">Waktu Masuk / Registrasi</th>
                                <th class="text-center px-4 py-2.5 font-bold">Aksi Operasional</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zcBrd bg-white">
                            <?php foreach ($grp['units'] as $s): 
                                $statusCls = match($s['status']) {
                                    'Tersedia'    => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
                                    'Terjual'     => 'bg-slate-100 text-slate-700 border border-slate-200',
                                    'Retur/Rusak' => 'bg-rose-100 text-rose-700 border border-rose-200',
                                    default       => 'bg-slate-100 text-slate-600 border border-slate-200',
                                };
                            ?>
                            <tr class="hover:bg-slate-50/70 transition <?= $s['status'] === 'Retur/Rusak' ? 'bg-rose-50/30' : '' ?>">
                                <td class="text-center px-3 py-3">
                                    <input type="checkbox" class="sn-item-chk rounded text-zc focus:ring-zc h-4 w-4 cursor-pointer"
                                           data-type="sn"
                                           data-sn="<?= htmlspecialchars($s['serial_number']) ?>"
                                           data-name="<?= htmlspecialchars($s['nama_produk'] . ' - ' . $s['nama_variasi']) ?>"
                                           onchange="toggleSNItem(this)">
                                </td>
                                <td class="px-4 py-3 font-mono font-bold text-zcTxt text-xs">
                                    <?= htmlspecialchars($s['serial_number']) ?>
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
                                                    echo '<span class="text-emerald-700 font-bold ml-1 px-1.5 py-0.5 rounded bg-emerald-50 border border-emerald-200">&bull; Garansi Aktif (' . $sisaGaransi . ' hari lagi)</span>';
                                                } else {
                                                    echo '<span class="text-slate-500 font-semibold ml-1 px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200">&bull; Garansi Habis</span>';
                                                }
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($s['status'] === 'Retur/Rusak'): ?>
                                        <span class="text-rose-600 font-semibold text-xs"><?= htmlspecialchars($s['catatan'] ?? 'Unit Rusak / Karantina') ?></span>
                                    <?php else: ?>
                                        <span class="text-emerald-700 font-semibold italic text-[11px]">Siap Jual (Belum Ada Transaksi)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-zcMut text-[11px] font-medium">
                                    <?= date('d M Y', strtotime($s['created_at'])) ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick="window.open('cetak_batch_stiker.php?tipe=sn&id=<?= urlencode($s['serial_number']) ?>', '_blank')" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 rounded-lg text-[10px] font-bold transition flex items-center gap-1 shadow-2xs" title="Cetak Stiker Barcode Unit Ini">
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
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination Tab 2 (Per Kelompok Produk Alkes) -->
        <?php if ($totalGroupsSN > 0): ?>
        <div class="px-5 py-3.5 bg-slate-50 rounded-2xl border border-zcBrd flex flex-col sm:flex-row items-center justify-between gap-3 text-xs mb-8">
            <span class="text-zcMut font-medium">
                Menampilkan <?= ($pageSN - 1) * $perPage + 1 ?> – <?= min($pageSN * $perPage, $totalGroupsSN) ?> dari <?= $totalGroupsSN ?> kelompok produk alkes (Total <?= $totalSN ?> unit SN)
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
    <?php endif; ?>
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

<!-- FLOATING ACTION DRAWER: KERANJANG CETAK STIKER LINTAS PRODUK & FILTER -->
<div id="sticker_basket_drawer" class="fixed bottom-5 left-1/2 -translate-x-1/2 z-50 bg-slate-900/95 backdrop-blur-md text-white rounded-2xl shadow-2xl px-5 py-3 border border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4 max-w-4xl w-[94%] transition-all duration-300 translate-y-32 opacity-0 pointer-events-none">
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 shrink-0">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        </div>
        <div>
            <div class="text-xs font-bold text-white flex items-center gap-2">
                <span>Keranjang Cetak Stiker</span>
                <span id="basket_badge_count" class="px-2 py-0.5 rounded-full bg-blue-600 font-bold text-[11px] text-white">0</span>
            </div>
            <div class="text-[11px] text-slate-400 flex items-center gap-2 mt-0.5">
                <span id="basket_detail_text">0 item dipilih</span>
                <span class="text-slate-600">&bull;</span>
                <span class="text-emerald-400 font-medium text-[10px]">Tersimpan Lintas Filter &amp; Halaman</span>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
        <button type="button" onclick="clearStickerBasket()" class="px-3 py-1.5 text-xs text-slate-400 hover:text-white transition font-medium">
            Kosongkan
        </button>
        <button type="button" onclick="checkoutBasket('thermal')" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-600 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs">
            <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6v12M7 6v12M11 6v12M14 6v12M17 6v12M20 6v12"/></svg>
            <span>Thermal Roll</span>
        </button>
        <button type="button" onclick="checkoutBasket('a4')" class="px-4 py-2 bg-zc hover:bg-zcHv text-white rounded-xl text-xs font-semibold transition flex items-center gap-1.5 shadow-sm shadow-blue-500/25">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            <span>Cetak Kertas A4</span>
        </button>
    </div>
</div>

<script>
// ─── ACCORDION TOGGLE LOGIC ───────────────────────────────────────────────
function toggleAccordion(groupId) {
    const body = document.getElementById('group_body_' + groupId);
    const icon = document.getElementById('group_icon_' + groupId);
    if (!body) return;
    
    if (body.classList.contains('hidden')) {
        body.classList.remove('hidden');
        if (icon) icon.classList.add('rotate-180');
    } else {
        body.classList.add('hidden');
        if (icon) icon.classList.remove('rotate-180');
    }
}

function expandAllGroups() {
    document.querySelectorAll('.accordion-body').forEach(b => b.classList.remove('hidden'));
    document.querySelectorAll('.accordion-icon').forEach(i => i.classList.add('rotate-180'));
}

function collapseAllGroups() {
    document.querySelectorAll('.accordion-body').forEach(b => b.classList.add('hidden'));
    document.querySelectorAll('.accordion-icon').forEach(i => i.classList.remove('rotate-180'));
}

// ─── MODAL KARANTINA ─────────────────────────────────────────────────────
function openKarantinaModal(id, sn, produk) {
    document.getElementById('modal_id_sn').value = id;
    document.getElementById('modal_sn_text').innerText = sn;
    document.getElementById('modal_produk_text').innerText = produk;
    document.getElementById('modalKarantina').classList.remove('hidden');
}
function closeKarantinaModal() {
    document.getElementById('modalKarantina').classList.add('hidden');
}

// ─── PERSISTENT STICKER BASKET (LINTAS PRODUK, FILTER & HALAMAN) ─────────
const BASKET_STORAGE_KEY = 'zencare_sticker_basket';

function getBasket() {
    try {
        const raw = sessionStorage.getItem(BASKET_STORAGE_KEY);
        return raw ? JSON.parse(raw) : {};
    } catch (e) {
        return {};
    }
}

function saveBasket(basket) {
    try {
        sessionStorage.setItem(BASKET_STORAGE_KEY, JSON.stringify(basket));
    } catch (e) {
        console.error("Gagal menyimpan keranjang cetak:", e);
    }
    updateBasketUI();
}

function clearStickerBasket() {
    sessionStorage.removeItem(BASKET_STORAGE_KEY);
    document.querySelectorAll('.batch-item-chk, .sn-item-chk, .group-select-all, #check_all_page').forEach(c => {
        c.checked = false;
        c.indeterminate = false;
    });
    updateBasketUI();
}

function toggleBatchItem(chk) {
    const basket = getBasket();
    const id = chk.dataset.id;
    const key = 'batch_' + id;
    
    if (chk.checked) {
        const row = chk.closest('tr');
        const qtyInp = row ? row.querySelector('.batch-item-qty') : null;
        const qty = qtyInp ? Math.max(1, parseInt(qtyInp.value, 10) || 1) : 1;
        basket[key] = {
            tipe: 'batch',
            id: id,
            nama: chk.dataset.name,
            kode: chk.dataset.code,
            exp: chk.dataset.exp,
            qty: qty
        };
    } else {
        delete basket[key];
    }
    saveBasket(basket);
    syncGroupCheckboxes();
}

function updateBatchQty(input, id) {
    const basket = getBasket();
    const key = 'batch_' + id;
    const qty = Math.max(1, parseInt(input.value, 10) || 1);
    if (basket[key]) {
        basket[key].qty = qty;
        saveBasket(basket);
    }
}

function toggleSNItem(chk) {
    const basket = getBasket();
    const sn = chk.dataset.sn;
    const key = 'sn_' + sn;
    
    if (chk.checked) {
        basket[key] = {
            tipe: 'sn',
            id: sn,
            nama: chk.dataset.name,
            kode: sn,
            exp: 'Serial Number',
            qty: 1
        };
    } else {
        delete basket[key];
    }
    saveBasket(basket);
    syncGroupCheckboxes();
}

function toggleGroupBatches(groupChk, groupId) {
    const basket = getBasket();
    const container = document.getElementById('group_body_' + groupId);
    if (!container) return;
    
    const checkboxes = container.querySelectorAll('.batch-item-chk');
    checkboxes.forEach(chk => {
        chk.checked = groupChk.checked;
        const id = chk.dataset.id;
        const key = 'batch_' + id;
        if (groupChk.checked) {
            const row = chk.closest('tr');
            const qtyInp = row ? row.querySelector('.batch-item-qty') : null;
            const qty = qtyInp ? Math.max(1, parseInt(qtyInp.value, 10) || 1) : 1;
            basket[key] = {
                tipe: 'batch',
                id: id,
                nama: chk.dataset.name,
                kode: chk.dataset.code,
                exp: chk.dataset.exp,
                qty: qty
            };
        } else {
            delete basket[key];
        }
    });
    saveBasket(basket);
}

function toggleGroupSN(groupChk, groupId) {
    const basket = getBasket();
    const container = document.getElementById('group_body_' + groupId);
    if (!container) return;
    
    const checkboxes = container.querySelectorAll('.sn-item-chk');
    checkboxes.forEach(chk => {
        chk.checked = groupChk.checked;
        const sn = chk.dataset.sn;
        const key = 'sn_' + sn;
        if (groupChk.checked) {
            basket[key] = {
                tipe: 'sn',
                id: sn,
                nama: chk.dataset.name,
                kode: sn,
                exp: 'Serial Number',
                qty: 1
            };
        } else {
            delete basket[key];
        }
    });
    saveBasket(basket);
}

function toggleSelectAllPage(checked) {
    const basket = getBasket();
    document.querySelectorAll('.batch-item-chk').forEach(chk => {
        chk.checked = checked;
        const id = chk.dataset.id;
        const key = 'batch_' + id;
        if (checked) {
            const row = chk.closest('tr');
            const qtyInp = row ? row.querySelector('.batch-item-qty') : null;
            const qty = qtyInp ? Math.max(1, parseInt(qtyInp.value, 10) || 1) : 1;
            basket[key] = {
                tipe: 'batch',
                id: id,
                nama: chk.dataset.name,
                kode: chk.dataset.code,
                exp: chk.dataset.exp,
                qty: qty
            };
        } else {
            delete basket[key];
        }
    });
    
    document.querySelectorAll('.sn-item-chk').forEach(chk => {
        chk.checked = checked;
        const sn = chk.dataset.sn;
        const key = 'sn_' + sn;
        if (checked) {
            basket[key] = {
                tipe: 'sn',
                id: sn,
                nama: chk.dataset.name,
                kode: sn,
                exp: 'Serial Number',
                qty: 1
            };
        } else {
            delete basket[key];
        }
    });
    
    document.querySelectorAll('.group-select-all').forEach(c => {
        c.checked = checked;
        c.indeterminate = false;
    });
    saveBasket(basket);
}

function printSingleBatch(btn, id) {
    const row = btn.closest('tr');
    const qtyInp = row ? row.querySelector('.batch-item-qty') : null;
    const qty = qtyInp ? Math.max(1, parseInt(qtyInp.value, 10) || 1) : 1;
    window.open(`cetak_batch_stiker.php?tipe=batch&id=${id}&qty=${qty}`, '_blank');
}

function syncBasketWithDOM() {
    const basket = getBasket();
    
    document.querySelectorAll('.batch-item-chk').forEach(chk => {
        const key = 'batch_' + chk.dataset.id;
        if (basket[key]) {
            chk.checked = true;
            const row = chk.closest('tr');
            const qtyInp = row ? row.querySelector('.batch-item-qty') : null;
            if (qtyInp && basket[key].qty) {
                qtyInp.value = basket[key].qty;
            }
        } else {
            chk.checked = false;
        }
    });
    
    document.querySelectorAll('.sn-item-chk').forEach(chk => {
        const key = 'sn_' + chk.dataset.sn;
        chk.checked = !!basket[key];
    });
    
    syncGroupCheckboxes();
    updateBasketUI();
}

function syncGroupCheckboxes() {
    document.querySelectorAll('.group-accordion').forEach(card => {
        const groupChk = card.querySelector('.group-select-all');
        const itemChks = card.querySelectorAll('.batch-item-chk, .sn-item-chk');
        if (groupChk && itemChks.length > 0) {
            const allChecked = Array.from(itemChks).every(c => c.checked);
            const someChecked = Array.from(itemChks).some(c => c.checked);
            groupChk.checked = allChecked;
            groupChk.indeterminate = someChecked && !allChecked;
        }
    });
    
    const pageChk = document.getElementById('check_all_page');
    const allPageItems = document.querySelectorAll('.batch-item-chk, .sn-item-chk');
    if (pageChk && allPageItems.length > 0) {
        const allChecked = Array.from(allPageItems).every(c => c.checked);
        const someChecked = Array.from(allPageItems).some(c => c.checked);
        pageChk.checked = allChecked;
        pageChk.indeterminate = someChecked && !allChecked;
    }
}

function updateBasketUI() {
    const basket = getBasket();
    const count = Object.keys(basket).length;
    let totalQty = 0;
    Object.values(basket).forEach(item => totalQty += (item.qty || 1));
    
    const drawer = document.getElementById('sticker_basket_drawer');
    const badge = document.getElementById('basket_badge_count');
    const detail = document.getElementById('basket_detail_text');
    
    if (badge) badge.innerText = count;
    if (detail) detail.innerText = `${count} item dipilih (${totalQty} label)`;
    
    if (drawer) {
        if (count > 0) {
            drawer.classList.remove('translate-y-32', 'opacity-0', 'pointer-events-none');
            drawer.classList.add('translate-y-0', 'opacity-100');
        } else {
            drawer.classList.add('translate-y-32', 'opacity-0', 'pointer-events-none');
            drawer.classList.remove('translate-y-0', 'opacity-100');
        }
    }
}

function checkoutBasket(mode = 'a4', targetUrl = 'cetak_batch_stiker.php') {
    const basket = getBasket();
    const keys = Object.keys(basket);
    if (keys.length === 0) {
        alert('Keranjang cetak masih kosong. Silakan centang minimal 1 batch atau nomor seri terlebih dahulu.');
        return;
    }
    
    const items = Object.values(basket).map(item => ({
        tipe: item.tipe,
        id: item.id,
        qty: item.qty || 1
    }));
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `${targetUrl}?mode=${mode}`;
    form.target = '_blank';
    
    const inp = document.createElement('input');
    inp.type = 'hidden';
    inp.name = 'selected_items';
    inp.value = JSON.stringify(items);
    
    form.appendChild(inp);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

// Inisialisasi sinkronisasi saat halaman selesai dimuat
document.addEventListener('DOMContentLoaded', function() {
    syncBasketWithDOM();
});
</script>

<?php
layoutFooter();
?>
