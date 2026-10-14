<?php
// File: admin/master_uom.php
// Master Data Satuan & UOM (Unit of Measure) — ZenCare Medical v3.0
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin', 'admin']);

$msg = ''; $msgType = '';

// ─── POST Handler ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action'] ?? '';
    $idVariasi = intval($_POST['id_variasi'] ?? 0);

    if ($action === 'update_uom' && $idVariasi) {
        $satuanKecil  = trim($_POST['satuan_kecil'] ?? '');
        $satuanBesar  = trim($_POST['satuan_besar'] ?? '');
        $rasio        = max(1, intval($_POST['rasio_konversi'] ?? 1));
        $hargaKecil   = floatval(str_replace(['.', ','], ['', '.'], $_POST['harga_jual_kecil'] ?? 0));
        $hargaBesar   = floatval(str_replace(['.', ','], ['', '.'], $_POST['harga_jual_besar'] ?? 0));
        
        if ($_SESSION['role'] !== 'superadmin') {
            $existing = $pdo->prepare("SELECT rasio_konversi, harga_jual_kecil, harga_jual_besar FROM produk_variasi WHERE id = ?");
            $existing->execute([$idVariasi]);
            if ($ex = $existing->fetch()) {
                $rasio = $ex['rasio_konversi'];
                $hargaKecil = $ex['harga_jual_kecil'];
                $hargaBesar = $ex['harga_jual_besar'];
            }
        }

        $stokMinKecil = max(0, intval($_POST['stok_minimum_kecil'] ?? 0));
        $stokMinBesar = max(0, intval($_POST['stok_minimum_besar'] ?? 0));
        $berat        = max(0, intval($_POST['berat'] ?? 0));

        if (!$satuanKecil || !$satuanBesar) {
            $msg = 'Satuan kecil dan satuan besar wajib diisi.'; $msgType = 'error';
        } else {
            $pdo->prepare("
                UPDATE produk_variasi SET 
                    satuan_kecil = ?, satuan_besar = ?, rasio_konversi = ?,
                    harga_jual_kecil = ?, harga_jual_besar = ?,
                    stok_minimum_kecil = ?, stok_minimum_besar = ?, berat = ?
                WHERE id = ?
            ")->execute([$satuanKecil, $satuanBesar, $rasio, $hargaKecil, $hargaBesar, $stokMinKecil, $stokMinBesar, $berat, $idVariasi]);
            $msg = 'Satuan & UOM berhasil diperbarui.'; $msgType = 'success';
        }
    }
}

// ─── FETCH DATA WITH FILTER, GROUPING BY PRODUK INDUK & PAGINATION ──────────
$katFilter = $_GET['kategori'] ?? 'semua';
$searchQ   = trim($_GET['q'] ?? '');

$where = ["pv.is_active = 1", "pi.is_active = 1"];
$params = [];

if ($katFilter === 'Obat') {
    $where[] = "pi.kategori = 'Obat'";
} elseif ($katFilter === 'Alat Kesehatan') {
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

// Counts for filter badges
$countObat  = $pdo->query("SELECT COUNT(DISTINCT pi.id) FROM produk_induk pi JOIN produk_variasi pv ON pi.id = pv.id_produk_induk WHERE pv.is_active = 1 AND pi.is_active = 1 AND pi.kategori = 'Obat'")->fetchColumn();
$countAlkes = $pdo->query("SELECT COUNT(DISTINCT pi.id) FROM produk_induk pi JOIN produk_variasi pv ON pi.id = pv.id_produk_induk WHERE pv.is_active = 1 AND pi.is_active = 1 AND pi.kategori = 'Alat Kesehatan'")->fetchColumn();
$countTotal = $pdo->query("SELECT COUNT(DISTINCT pi.id) FROM produk_induk pi JOIN produk_variasi pv ON pi.id = pv.id_produk_induk WHERE pv.is_active = 1 AND pi.is_active = 1")->fetchColumn();

// Fetch all filtered rows to group by parent
$dataSql = "
    SELECT pv.*, pi.id AS id_induk, pi.nama_produk, pi.kategori, pi.sku_induk
    FROM produk_variasi pv
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk
    WHERE $whereClause
    ORDER BY 
        CASE WHEN pi.kategori = 'Obat' THEN 1 WHEN pi.kategori = 'Alat Kesehatan' THEN 2 ELSE 3 END ASC,
        pi.nama_produk ASC,
        pv.nama_variasi ASC
";
$stmtData = $pdo->prepare($dataSql);
$stmtData->execute($params);
$allRows = $stmtData->fetchAll(PDO::FETCH_ASSOC);

// Grouping by Produk Induk
$parentProducts = [];
$totalVariasiCount = count($allRows);

foreach ($allRows as $r) {
    $idInduk = $r['id_induk'];
    if (!isset($parentProducts[$idInduk])) {
        $parentProducts[$idInduk] = [
            'id_induk'     => $idInduk,
            'nama_produk'  => $r['nama_produk'],
            'kategori'     => $r['kategori'],
            'sku_induk'    => $r['sku_induk'] ?? '',
            'variasi_list' => []
        ];
    }
    $parentProducts[$idInduk]['variasi_list'][] = $r;
}

$parents = array_values($parentProducts);
$totalParents = count($parents);

// Pagination (15 Produk Induk per page)
$perPage = 15;
$page = max(1, intval($_GET['page'] ?? 1));
$totalPages = max(1, (int)ceil($totalParents / $perPage));
if ($page > $totalPages) $page = $totalPages;
$parentsPageList = array_slice($parents, ($page - 1) * $perPage, $perPage);

layoutHead('Master Satuan & UOM');
layoutBodyOpen();
layoutSidebar('master_uom');
layoutHeader('Master Satuan & UOM', 'Pengaturan Unit of Measure & Harga Berbasis Produk Induk');
?>

<?php if ($msg): ?>
<div class="mb-5 flex items-center gap-3 p-3.5 rounded-xl text-sm font-medium <?= $msgType === 'error' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-emerald-50 border border-emerald-200 text-emerald-700' ?>">
    <?= $msg ?>
</div>
<?php endif; ?>

<!-- CATEGORY FILTER TABS -->
<div class="flex items-center gap-2 mb-4 flex-wrap">
    <a href="?kategori=semua<?= $searchQ ? '&q=' . urlencode($searchQ) : '' ?>" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $katFilter === 'semua' ? 'bg-zc text-white shadow-xs' : 'bg-white border border-zcBrd text-zcTxt hover:bg-slate-50' ?>">
        Semua Produk (<?= $countTotal ?>)
    </a>
    <a href="?kategori=Obat<?= $searchQ ? '&q=' . urlencode($searchQ) : '' ?>" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $katFilter === 'Obat' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white border border-zcBrd text-indigo-700 hover:bg-indigo-50/50' ?>">
        Obat (<?= $countObat ?>)
    </a>
    <a href="?kategori=Alat+Kesehatan<?= $searchQ ? '&q=' . urlencode($searchQ) : '' ?>" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $katFilter === 'Alat Kesehatan' ? 'bg-teal-600 text-white shadow-xs' : 'bg-white border border-zcBrd text-teal-700 hover:bg-teal-50/50' ?>">
        Alat Kesehatan (<?= $countAlkes ?>)
    </a>
</div>

<!-- ACTION & SEARCH HEADER -->
<div class="flex items-center justify-between mb-4 flex-wrap gap-3">
    <div>
        <h2 class="text-sm font-bold text-zcTxt">Daftar Satuan &amp; UOM Berbasis Produk Induk</h2>
        <p class="text-xs text-zcMut mt-0.5">Total: <?= $totalParents ?> Produk Induk (<?= $totalVariasiCount ?> SKU Variasi) &bull; Klik &blacktriangledown; untuk melihat dan mengatur UOM variasi</p>
    </div>
    <form method="GET" class="flex items-center gap-2">
        <input type="hidden" name="kategori" value="<?= htmlspecialchars($katFilter) ?>">
        <div class="relative w-full md:max-w-xs">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </span>
            <input type="text" name="q" id="searchUom" value="<?= htmlspecialchars($searchQ) ?>" placeholder="Cari nama atau SKU..." 
                   class="w-full pl-9 pr-4 py-2 bg-white border border-zcBrd rounded-xl text-xs focus:outline-none focus:border-zc shadow-sm">
        </div>
        <button type="submit" class="px-3.5 py-2 bg-zc hover:bg-zcHv text-white rounded-xl text-xs font-bold transition shadow-sm">Cari</button>
        <?php if ($searchQ): ?>
            <a href="?kategori=<?= urlencode($katFilter) ?>" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Info Box & Expand All Buttons -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-3 flex-1 flex gap-2.5">
        <svg class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <div class="text-[11px] text-blue-700 leading-tight">
            <strong>Aturan UOM:</strong> <strong>Obat</strong> memiliki rasio konversi grosir &gt; 1 (1 Box = X Strip/Botol). <strong>Alat Kesehatan</strong> menggunakan Single-UOM (rasio 1:1).
        </div>
    </div>
    <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
        <button type="button" onclick="toggleAllUom(true)" class="px-3 py-2 bg-white border border-zcBrd text-zcTxt text-xs font-bold rounded-xl hover:bg-slate-50 transition shadow-2xs">
            &darr; Buka Semua
        </button>
        <button type="button" onclick="toggleAllUom(false)" class="px-3 py-2 bg-white border border-zcBrd text-zcTxt text-xs font-bold rounded-xl hover:bg-slate-50 transition shadow-2xs">
            &uarr; Tutup Semua
        </button>
    </div>
</div>

<!-- List Produk Induk Accordion -->
<div class="space-y-3.5 mb-8">
    <?php if (empty($parentsPageList)): ?>
        <div class="bg-white rounded-2xl border border-zcBrd p-12 text-center text-zcMut">
            <p class="font-bold text-sm text-zcTxt mb-1">Tidak ada data produk ditemukan.</p>
            <p class="text-xs text-zcMut">Coba ubah kata kunci pencarian atau kategori filter.</p>
        </div>
    <?php else: ?>
        <?php foreach ($parentsPageList as $pIdx => $pi): 
            $katBadge = ($pi['kategori'] === 'Obat') ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-teal-50 border-teal-200 text-teal-700';
        ?>
        <div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden transition duration-150">
            
            <!-- Parent Header -->
            <div onclick="toggleUomAcc('uom_acc_<?= $pi['id_induk'] ?>')" class="p-4 cursor-pointer hover:bg-slate-50/70 flex items-center justify-between gap-3 select-none">
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
                        <p class="text-[11px] text-zcMut mt-0.5">
                            <?= count($pi['variasi_list']) ?> variasi kemasan terdaftar
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <span class="text-xs font-semibold text-zc font-mono bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-100">
                        <?= count($pi['variasi_list']) ?> SKU
                    </span>
                    <div id="uom_icon_<?= $pi['id_induk'] ?>" class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 font-bold transition-transform text-xs">
                        &#9660;
                    </div>
                </div>
            </div>

            <!-- Variations Table (Collapsible) -->
            <div id="uom_acc_<?= $pi['id_induk'] ?>" class="uom-acc-content hidden border-t border-zcBrd bg-slate-50/60 p-4">
                <div class="overflow-x-auto bg-white rounded-xl border border-zcBrd shadow-2xs">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 border-b border-zcBrd">
                            <tr>
                                <th class="text-left px-3 py-2.5 font-bold text-zcMut">SKU Variasi</th>
                                <th class="text-left px-3 py-2.5 font-bold text-zcMut">Nama Variasi</th>
                                <th class="text-center px-3 py-2.5 font-bold text-zcMut">Satuan Kecil</th>
                                <th class="text-center px-3 py-2.5 font-bold text-zcMut">Satuan Besar</th>
                                <th class="text-center px-3 py-2.5 font-bold text-zcMut">Rasio</th>
                                <th class="text-right px-3 py-2.5 font-bold text-zcMut">Harga Ecer (POS)</th>
                                <th class="text-right px-3 py-2.5 font-bold text-zcMut">Harga Grosir (Web)</th>
                                <th class="text-right px-3 py-2.5 font-bold text-zcMut">Stok Min</th>
                                <th class="text-center px-3 py-2.5 font-bold text-zcMut">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zcBrd">
                            <?php foreach ($pi['variasi_list'] as $v): ?>
                            <tr class="hover:bg-slate-50/70 transition" id="row-<?= $v['id'] ?>">
                                <td class="px-3 py-2.5 font-mono text-[11px] text-slate-600 font-semibold"><?= htmlspecialchars($v['sku_variasi']) ?></td>
                                <td class="px-3 py-2.5 font-bold text-zcTxt"><?= htmlspecialchars($v['nama_variasi']) ?></td>
                                <td class="px-3 py-2.5 text-center font-semibold text-zcTxt"><?= htmlspecialchars($v['satuan_kecil']) ?></td>
                                <td class="px-3 py-2.5 text-center font-semibold text-zcTxt"><?= htmlspecialchars($v['satuan_besar']) ?></td>
                                <td class="px-3 py-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 font-bold text-[11px]">1:<?= $v['rasio_konversi'] ?></span>
                                </td>
                                <td class="px-3 py-2.5 text-right font-semibold text-zcTxt">Rp <?= number_format($v['harga_jual_kecil'], 0, ',', '.') ?></td>
                                <td class="px-3 py-2.5 text-right font-semibold text-emerald-700">Rp <?= number_format($v['harga_jual_besar'], 0, ',', '.') ?></td>
                                <td class="px-3 py-2.5 text-right">
                                    <div class="text-[10px]">Kecil: <span class="font-bold <?= $v['stok_minimum_kecil'] > 0 ? 'text-amber-600' : 'text-slate-400' ?>"><?= $v['stok_minimum_kecil'] ?></span></div>
                                    <div class="text-[10px]">Besar: <span class="font-bold <?= $v['stok_minimum_besar'] > 0 ? 'text-amber-600' : 'text-slate-400' ?>"><?= $v['stok_minimum_besar'] ?></span></div>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <button onclick='openEditModal(<?= htmlspecialchars(json_encode($v), ENT_QUOTES) ?>)'
                                        class="px-2.5 py-1 bg-zcLt text-zc rounded-lg hover:bg-blue-100 font-bold text-[11px] transition shadow-2xs">
                                        Edit UOM
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Pagination Controls -->
<?php if ($totalParents > 0): ?>
<div class="p-4 bg-white rounded-2xl border border-zcBrd flex flex-col sm:flex-row items-center justify-between gap-3 text-xs shadow-xs mb-8">
    <span class="text-zcMut font-medium">
        Menampilkan <?= ($page - 1) * $perPage + 1 ?> – <?= min($page * $perPage, $totalParents) ?> dari <?= $totalParents ?> Produk Induk
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

<!-- Modal Edit UOM -->
<div id="modal-edit-uom" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full">
        <div class="p-5 border-b border-zcBrd flex items-center justify-between">
            <h3 class="text-sm font-bold text-zcTxt" id="modal-title">Edit UOM</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-red-500">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form method="POST" class="p-5 space-y-4">
            <input type="hidden" name="action" value="update_uom">
            <input type="hidden" name="id_variasi" id="edit_id_variasi">

            <div id="alkes-note" class="hidden bg-purple-50 border border-purple-200 rounded-xl p-3 text-xs text-purple-700 font-medium">
                <strong>Alat Kesehatan (Single-UOM)</strong>: Rasio konversi otomatis = 1. Harga besar = harga kecil.
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Satuan Kecil *</label>
                    <input type="text" name="satuan_kecil" id="edit_satuan_kecil" required placeholder="Botol / Strip / Pcs"
                        class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Satuan Besar *</label>
                    <input type="text" name="satuan_besar" id="edit_satuan_besar" required placeholder="Box / Dus / Karton"
                        class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zcTxt mb-1.5 flex justify-between">Rasio Konversi (1 Besar = ? Kecil) * <?= $_SESSION['role'] !== 'superadmin' ? '<span class="text-[9px] text-rose-500 font-bold bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">Terkunci</span>' : '' ?></label>
                <input type="number" name="rasio_konversi" id="edit_rasio" min="1" required
                    <?= $_SESSION['role'] !== 'superadmin' ? 'readonly class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 bg-slate-100 text-slate-500 cursor-not-allowed focus:outline-none"' : 'class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc"' ?>>
                <p class="text-[10px] text-zcMut mt-1">Obat: sesuai kemasan (mis. 50 = 1 Karton isi 50 Botol). Alkes: wajib 1.</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5 flex justify-between">Harga Jual Kecil (POS) * <?= $_SESSION['role'] !== 'superadmin' ? '<span class="text-[9px] text-rose-500 font-bold bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">Terkunci</span>' : '' ?></label>
                    <input type="number" name="harga_jual_kecil" id="edit_harga_kecil" min="0" step="100" required
                        <?= $_SESSION['role'] !== 'superadmin' ? 'readonly class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 bg-slate-100 text-slate-500 cursor-not-allowed focus:outline-none"' : 'class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc"' ?>>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5 flex justify-between">Harga Jual Besar (E-Commerce) * <?= $_SESSION['role'] !== 'superadmin' ? '<span class="text-[9px] text-rose-500 font-bold bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">Terkunci</span>' : '' ?></label>
                    <input type="number" name="harga_jual_besar" id="edit_harga_besar" min="0" step="100" required
                        <?= $_SESSION['role'] !== 'superadmin' ? 'readonly class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2.5 bg-slate-100 text-slate-500 cursor-not-allowed focus:outline-none"' : 'class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc"' ?>>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Stok Min (Eceran)</label>
                    <input type="number" name="stok_minimum_kecil" id="edit_stok_min_kecil" min="0"
                        class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
                    
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5 mt-2">Stok Min (Grosir)</label>
                    <input type="number" name="stok_minimum_besar" id="edit_stok_min_besar" min="0"
                        class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Berat (gram)</label>
                    <input type="number" name="berat" id="edit_berat" min="0"
                        class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
                    <p class="text-[10px] text-zcMut mt-1">Untuk kalkulasi ongkos kirim ekspedisi</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal()" class="px-5 py-2 rounded-xl border border-zcBrd text-zcMut text-xs font-semibold">Batal</button>
                <button type="submit" class="px-6 py-2 rounded-xl bg-zc hover:bg-zcHv text-white text-xs font-bold shadow-sm shadow-blue-500/20">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleUomAcc(id) {
    const el = document.getElementById(id);
    const iconId = id.replace('uom_acc_', 'uom_icon_');
    const icon = document.getElementById(iconId);
    if (!el) return;
    
    if (el.classList.contains('hidden')) {
        el.classList.remove('hidden');
        if (icon) icon.style.transform = 'rotate(180deg)';
    } else {
        el.classList.add('hidden');
        if (icon) icon.style.transform = 'rotate(0deg)';
    }
}

function toggleAllUom(expand) {
    document.querySelectorAll('.uom-acc-content').forEach(el => {
        const iconId = el.id.replace('uom_acc_', 'uom_icon_');
        const icon = document.getElementById(iconId);
        if (expand) {
            el.classList.remove('hidden');
            if (icon) icon.style.transform = 'rotate(180deg)';
        } else {
            el.classList.add('hidden');
            if (icon) icon.style.transform = 'rotate(0deg)';
        }
    });
}

function openEditModal(data) {
    document.getElementById('edit_id_variasi').value = data.id;
    document.getElementById('modal-title').textContent = 'Edit UOM: ' + data.nama_produk + ' (' + data.nama_variasi + ')';
    document.getElementById('edit_satuan_kecil').value = data.satuan_kecil;
    document.getElementById('edit_satuan_besar').value = data.satuan_besar;
    document.getElementById('edit_rasio').value = data.rasio_konversi;
    document.getElementById('edit_harga_kecil').value = data.harga_jual_kecil;
    document.getElementById('edit_harga_besar').value = data.harga_jual_besar;
    document.getElementById('edit_stok_min_kecil').value = data.stok_minimum_kecil;
    document.getElementById('edit_stok_min_besar').value = data.stok_minimum_besar;
    document.getElementById('edit_berat').value = data.berat;
    
    const isAlkes = (parseInt(data.rasio_konversi) === 1);
    document.getElementById('alkes-note').classList.toggle('hidden', !isAlkes);
    document.getElementById('modal-edit-uom').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('modal-edit-uom').classList.add('hidden');
}
</script>

<?php layoutFooter(); ?>
