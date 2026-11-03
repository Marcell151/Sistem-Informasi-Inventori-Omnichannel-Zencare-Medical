<?php
// File: ecommerce/kategori.php — Katalog Kategori & Koleksi (Pharmify Collection Layout)
$pageTitle = "Katalog Produk";
require_once __DIR__ . '/header.php';

$search   = trim($_GET['q'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');
$indukId  = intval($_GET['induk'] ?? 0);

$sql = "
    SELECT v.id,
           i.id AS id_produk_induk,
           i.nama_produk AS nama_induk,
           v.nama_variasi,
           CONCAT(i.nama_produk, ' — ', v.nama_variasi) AS nama_produk,
           i.kategori,
           v.harga_jual_besar AS harga_jual,
           v.harga_jual_kecil AS harga_eceran,
           v.berat AS berat_gram,
           v.gambar AS gambar,
           v.sku_variasi,
           i.deskripsi,
           COALESCE(sc.stok, 0) AS stok_sistem,
           (SELECT COALESCE(SUM(sb.stok_sisa), 0) 
            FROM stok_batch sb 
            WHERE sb.id_variasi = v.id 
              AND sb.no_batch NOT LIKE '%.%' 
              AND sb.tgl_exp >= CURDATE()
           ) AS stok_batch_utuh,
           v.satuan_besar, v.satuan_kecil, v.rasio_konversi
    FROM produk_variasi v
    JOIN produk_induk i ON v.id_produk_induk = i.id
    LEFT JOIN stok_toko sc ON sc.id_variasi = v.id 
    WHERE v.is_active = 1 AND i.is_active = 1 AND v.tampil_di_online = 1
";
$params = [];

if ($search) {
    $sql .= " AND (i.nama_produk LIKE ? OR v.nama_variasi LIKE ? OR v.sku_variasi LIKE ? OR i.deskripsi LIKE ?)";
    $lk = "%$search%";
    array_push($params, $lk, $lk, $lk, $lk);
}
if ($kategori) {
    $sql .= " AND i.kategori = ?";
    $params[] = $kategori;
}
if ($indukId > 0) {
    $sql .= " AND i.id = ?";
    $params[] = $indukId;
}
$sql .= " ORDER BY i.kategori ASC, i.nama_produk ASC, v.id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Hitung ketersediaan stok untuk filter sidebar (In Stock vs Out of Stock)
$inStockCount  = 0;
$outStockCount = 0;
foreach ($products as $p) {
    $rasio = intval($p['rasio_konversi']) ?: 1;
    $stokBox = ($p['kategori'] === 'Obat')
        ? (int) floor(intval($p['stok_batch_utuh']) / $rasio)
        : (int) floor(intval($p['stok_sistem']) / $rasio);
    if ($stokBox > 0) $inStockCount++;
    else $outStockCount++;
}

// Ambil struktur Kategori & Produk Induk untuk Sidebar
$stmtSidebar = $pdo->query("
    SELECT i.id, i.nama_produk, i.kategori, COUNT(v.id) AS jml_variasi
    FROM produk_induk i
    JOIN produk_variasi v ON v.id_produk_induk = i.id
    WHERE i.is_active = 1 AND v.is_active = 1 AND v.tampil_di_online = 1
    GROUP BY i.id, i.nama_produk, i.kategori
    ORDER BY i.kategori ASC, i.nama_produk ASC
");
$sidebarInduk = [
    'Obat' => [],
    'Alat Kesehatan' => []
];
foreach ($stmtSidebar->fetchAll() as $row) {
    $sidebarInduk[$row['kategori']][] = $row;
}
?>

<style>
.prod-card .card-img-box {
    background-color: #f6f8fa;
    transition: background-color .25s ease;
}
.prod-card:hover .card-img-box {
    background-color: #eef4fa;
}
.prod-card .card-img-box img {
    transition: transform .35s cubic-bezier(.16,1,.3,1);
}
.prod-card:hover .card-img-box img {
    transform: scale(1.05);
}
.grid-col-btn.active {
    background-color: #0f2d5a;
    color: #ffffff;
    border-color: #0f2d5a;
}
</style>

<main class="flex-1 w-full bg-white">

    <!-- ============================================================ -->
    <!-- PHARMIFY SUBPAGE MOLECULAR BREADCRUMB BANNER                 -->
    <!-- ============================================================ -->
    <section class="pharmify-banner py-11 lg:py-14 text-center text-white">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 relative z-10">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight mb-2.5">
                <?php if ($search): ?>
                    Pencarian: "<?= htmlspecialchars($search) ?>"
                <?php elseif ($kategori): ?>
                    <?= htmlspecialchars($kategori) ?>
                <?php else: ?>
                    Katalog Produk Kesehatan
                <?php endif; ?>
            </h1>
            <nav class="flex items-center justify-center gap-2 text-xs font-medium text-sky-200/90">
                <a href="index.php" class="hover:text-white transition">Beranda</a>
                <span>&rsaquo;</span>
                <?php if ($kategori): ?>
                    <a href="kategori.php" class="hover:text-white transition">Katalog</a>
                    <span>&rsaquo;</span>
                    <span class="text-white font-semibold"><?= htmlspecialchars($kategori) ?></span>
                <?php else: ?>
                    <span class="text-white font-semibold">Semua Koleksi</span>
                <?php endif; ?>
            </nav>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2-COLUMN COLLECTION LAYOUT (Sidebar Left + Grid Right)       -->
    <!-- ============================================================ -->
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-8 sm:py-10 lg:py-12">
        
        <!-- Mobile Filter Trigger Button (Sticky / Prominent on < lg) -->
        <div class="lg:hidden mb-4">
            <button type="button" onclick="toggleMobileFilter()" class="w-full flex items-center justify-between px-4 py-3 bg-[#eef6fb] border border-[#cbe4f5] text-[#0f2d5a] font-bold text-xs uppercase tracking-wider transition hover:bg-[#dfeef9] shadow-2xs">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#1a75d2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filter &amp; Kategori Produk</span>
                </div>
                <span class="text-[11px] font-semibold text-[#1a75d2] flex items-center gap-1">
                    Buka Filter &rarr;
                </span>
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">

            <!-- LEFT SIDEBAR (Desktop only - Pharmify Reference 7) -->
            <aside class="hidden lg:block lg:col-span-3 space-y-8">
                
                <!-- 1. Category Accordion List -->
                <div>
                    <div class="border-b border-slate-200 pb-2.5 mb-4 relative">
                        <h3 class="text-base font-extrabold text-[#0f2d5a]">Kategori</h3>
                        <span class="absolute left-0 -bottom-[1px] w-10 h-[2px] bg-[#0f2d5a]"></span>
                    </div>

                    <ul class="space-y-2.5 text-sm">
                        <li>
                            <a href="kategori.php" class="flex items-center justify-between py-1 transition <?= (empty($kategori) && !$indukId) ? 'font-bold text-[#0f2d5a]' : 'text-slate-600 hover:text-[#0f2d5a]' ?>">
                                <span>Semua Koleksi Produk</span>
                                <span class="text-xs text-slate-400">(<?= count($sidebarInduk['Obat']) + count($sidebarInduk['Alat Kesehatan']) ?>)</span>
                            </a>
                        </li>

                        <?php foreach (['Obat' => 'Obat & Farmasi', 'Alat Kesehatan' => 'Alat Kesehatan Medis'] as $catKey => $catTitle): 
                            $isCatOpen = ($kategori === $catKey);
                        ?>
                        <li class="border-t border-slate-100 pt-2.5">
                            <div class="flex items-center justify-between">
                                <a href="kategori.php?kategori=<?= urlencode($catKey) ?>" class="py-0.5 transition <?= ($kategori === $catKey && !$indukId) ? 'font-bold text-[#0f2d5a]' : 'text-slate-700 hover:text-[#0f2d5a] font-medium' ?>">
                                    <?= htmlspecialchars($catTitle) ?>
                                </a>
                                <button type="button" onclick="toggleSidebarCat('sub_<?= md5($catKey) ?>', this)" class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-[#0f2d5a] font-bold text-base cursor-pointer">
                                    <?= $isCatOpen ? '−' : '+' ?>
                                </button>
                            </div>
                            <ul id="sub_<?= md5($catKey) ?>" class="<?= $isCatOpen ? '' : 'hidden' ?> mt-2 pl-3 border-l border-slate-200 space-y-1.5 text-xs">
                                <?php foreach ($sidebarInduk[$catKey] as $ind): ?>
                                <li>
                                    <a href="kategori.php?kategori=<?= urlencode($catKey) ?>&induk=<?= $ind['id'] ?>" class="block py-0.5 transition <?= ($indukId === intval($ind['id'])) ? 'font-bold text-[#1a75d2]' : 'text-slate-500 hover:text-[#0f2d5a]' ?>">
                                        <?= htmlspecialchars($ind['nama_produk']) ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- 2. Filter: Availability (Ketersediaan Stok) -->
                <div>
                    <div class="border-b border-slate-200 pb-2.5 mb-3 relative">
                        <h3 class="text-base font-extrabold text-[#0f2d5a]">Filter:</h3>
                        <span class="absolute left-0 -bottom-[1px] w-10 h-[2px] bg-[#0f2d5a]"></span>
                    </div>

                    <div class="py-2 border-b border-slate-200">
                        <div class="flex items-center justify-between text-xs font-bold text-[#0f2d5a] mb-2">
                            <span>Ketersediaan Stok</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-500 pb-2.5 mb-2.5 border-b border-dashed border-slate-200">
                            <span id="avail_selected_lbl">0 dipilih</span>
                            <button type="button" onclick="resetAvailabilityFilter()" class="text-[#0f2d5a] hover:underline font-semibold cursor-pointer">Reset</button>
                        </div>

                        <div class="space-y-2 text-xs text-slate-600">
                            <label class="flex items-center justify-between cursor-pointer hover:text-[#0f2d5a]">
                                <span class="flex items-center gap-2">
                                    <input type="checkbox" id="chk_instock" onchange="applyClientFilters()" class="w-3.5 h-3.5 accent-[#0f2d5a] rounded-none">
                                    <span>Tersedia (Stok Ada)</span>
                                </span>
                                <span class="text-slate-400">(<?= $inStockCount ?>)</span>
                            </label>
                            <label class="flex items-center justify-between cursor-pointer hover:text-[#0f2d5a]">
                                <span class="flex items-center gap-2">
                                    <input type="checkbox" id="chk_outstock" onchange="applyClientFilters()" class="w-3.5 h-3.5 accent-[#0f2d5a] rounded-none">
                                    <span>Stok Habis</span>
                                </span>
                                <span class="text-slate-400">(<?= $outStockCount ?>)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 3. Sidebar Search Box -->
                <div>
                    <div class="border-b border-slate-200 pb-2.5 mb-3 relative">
                        <h3 class="text-base font-extrabold text-[#0f2d5a]">Pencarian Cepat</h3>
                        <span class="absolute left-0 -bottom-[1px] w-10 h-[2px] bg-[#0f2d5a]"></span>
                    </div>
                    <form action="kategori.php" method="GET" class="space-y-2.5">
                        <?php if ($kategori): ?>
                            <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori) ?>">
                        <?php endif; ?>
                        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Ketik nama obat / alkes..." class="w-full px-3 py-2 text-xs border border-slate-200 focus:outline-none focus:border-[#0f2d5a]">
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 py-2 bg-[#0f2d5a] hover:bg-[#1a75d2] text-white text-xs font-bold uppercase tracking-wider transition cursor-pointer">
                                Cari
                            </button>
                            <?php if ($search || $kategori || $indukId): ?>
                            <a href="kategori.php" class="px-3 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                                Reset
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

            </aside>

            <!-- RIGHT MAIN PRODUCT GRID (Pharmify Reference 7) -->
            <div class="w-full lg:col-span-9">

                <!-- Top Sort & Column Switcher Toolbar -->
                <div class="bg-[#eef6fb] px-3 sm:px-4 py-3 mb-6 sm:mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <label for="sort_select" class="text-xs font-bold text-[#0f2d5a] shrink-0">Urutkan:</label>
                        <select id="sort_select" onchange="sortProducts()" class="text-xs border border-slate-200 bg-white px-3 py-2 text-[#0f2d5a] font-semibold focus:outline-none focus:border-[#0f2d5a] w-full sm:w-52">
                            <option value="default">Urutan Standar</option>
                            <option value="name_asc">Alfabetis, A–Z</option>
                            <option value="price_asc">Harga, Rendah ke Tinggi</option>
                            <option value="price_desc">Harga, Tinggi ke Rendah</option>
                            <option value="stok_desc">Stok Terbanyak</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-4 w-full sm:w-auto">
                        <span class="text-xs text-slate-600">
                            Menampilkan <strong id="visible_count" class="text-[#0f2d5a]"><?= count($products) ?></strong> produk
                        </span>

                        <!-- Grid Switcher Icons (3 cols vs 4 cols) -->
                        <div class="hidden md:flex items-center gap-1.5">
                            <button type="button" onclick="setGridColumns(3)" id="grid_btn_3" title="Tampilan 3 Kolom" class="grid-col-btn active w-8 h-8 border border-slate-200 bg-white text-slate-600 flex items-center justify-center transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="currentColor"><rect x="1" y="2" width="3" height="12"/><rect x="6.5" y="2" width="3" height="12"/><rect x="12" y="2" width="3" height="12"/></svg>
                            </button>
                            <button type="button" onclick="setGridColumns(4)" id="grid_btn_4" title="Tampilan 4 Kolom" class="grid-col-btn w-8 h-8 border border-slate-200 bg-white text-slate-600 flex items-center justify-center transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="currentColor"><rect x="0.5" y="2" width="2.5" height="12"/><rect x="4.8" y="2" width="2.5" height="12"/><rect x="9.1" y="2" width="2.5" height="12"/><rect x="13" y="2" width="2.5" height="12"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- PRODUCT GRID -->
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-6" id="product-grid">
                    <?php foreach ($products as $p):
                        $rasio    = intval($p['rasio_konversi']) ?: 1;
                        $stokBox  = ($p['kategori'] === 'Obat')
                            ? (int) floor(intval($p['stok_batch_utuh']) / $rasio)
                            : (int) floor(intval($p['stok_sistem']) / $rasio);
                        $harga    = floatval($p['harga_jual']);
                        $hargaEcr = floatval($p['harga_eceran']);
                        $satB     = htmlspecialchars($p['satuan_besar'] ?: 'Box');
                        $satK     = htmlspecialchars($p['satuan_kecil'] ?: 'Pcs');

                        if ($stokBox <= 0) {
                            $stokBadge = 'bg-rose-600 text-white';
                            $stokLbl   = 'Stok Habis';
                        } elseif ($stokBox <= 5) {
                            $stokBadge = 'bg-amber-500 text-white';
                            $stokLbl   = "Sisa $stokBox $satB";
                        } else {
                            $stokBadge = 'bg-white/90 text-[#0f2d5a] border border-slate-200/80';
                            $stokLbl   = "Stok: $stokBox $satB";
                        }

                        $rawGbr = $p['gambar'] ?? '';
                        $arrGbr = array_filter(array_map('trim', explode(',', $rawGbr)));
                        $firstGbr = $arrGbr[0] ?? '';
                        $firstSrc = '';
                        if (!empty($firstGbr)) {
                            if (str_starts_with($firstGbr, 'http')) $firstSrc = $firstGbr;
                            else if (str_contains($firstGbr, '/')) $firstSrc = '../' . $firstGbr;
                            else $firstSrc = '../assets/img/produk/' . $firstGbr;
                        }
                    ?>
                    <div class="prod-card group flex flex-col bg-white"
                         data-name="<?= strtolower(htmlspecialchars($p['nama_induk'])) ?>"
                         data-price="<?= $harga ?>"
                         data-stok="<?= $stokBox ?>"
                         data-id="<?= $p['id'] ?>">

                        <!-- Image Container -->
                        <a href="produk.php?id=<?= $p['id'] ?>" class="card-img-box relative block aspect-square w-full overflow-hidden mb-2 sm:mb-3.5">
                            <?php if (!empty($firstSrc)): ?>
                                <img src="<?= htmlspecialchars($firstSrc) ?>" alt="<?= htmlspecialchars($p['nama_induk']) ?>"
                                     class="w-full h-full object-contain p-3 sm:p-6 mix-blend-multiply"
                                     onerror="this.onerror=null; this.src='../assets/img/no-image.png';">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                    <svg class="w-10 h-10 sm:w-14 sm:h-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                                </div>
                            <?php endif; ?>

                            <span class="absolute top-2 left-2 sm:top-3 sm:left-3 px-1.5 sm:px-2.5 py-0.5 text-[9px] sm:text-[10px] font-semibold tracking-wide <?= $stokBadge ?>">
                                <?= $stokLbl ?>
                            </span>
                        </a>

                        <!-- Card Content -->
                        <div class="flex flex-col flex-1">
                            <div class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-0.5 sm:mb-1 truncate">
                                <?= htmlspecialchars($p['kategori']) ?> &bull; 1 <?= $satB ?><?= $rasio > 1 ? " ($rasio $satK)" : "" ?>
                            </div>

                            <a href="produk.php?id=<?= $p['id'] ?>" class="block mb-1.5 sm:mb-2">
                                <h3 class="text-xs sm:text-[15px] font-bold text-[#0f2d5a] group-hover:text-[#1a75d2] transition-colors leading-snug line-clamp-2">
                                    <?= htmlspecialchars($p['nama_induk']) ?>
                                </h3>
                                <p class="text-[11px] sm:text-xs text-slate-500 line-clamp-1 mt-0.5"><?= htmlspecialchars($p['nama_variasi']) ?></p>
                            </a>

                            <!-- Price -->
                            <div class="flex flex-wrap items-baseline gap-1 sm:gap-2 mb-2 sm:mb-3.5 mt-auto pt-1">
                                <span class="text-sm sm:text-base font-extrabold text-[#0f2d5a]">Rp <?= number_format($harga, 0, ',', '.') ?></span>
                                <span class="text-[10px] sm:text-xs text-slate-400 font-medium">/ <?= $satB ?></span>
                            </div>

                            <!-- Action Controls -->
                            <?php if ($stokBox >= 1): ?>
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <div class="flex items-center border border-slate-200 bg-[#f8fafc] h-8 sm:h-9 shrink-0">
                                    <button type="button" onclick="adjustQty(<?= $p['id'] ?>, -1, <?= $stokBox ?>)"
                                        class="w-5 sm:w-7 h-full flex items-center justify-center hover:bg-slate-200/60 text-slate-700 font-bold transition text-xs sm:text-sm cursor-pointer">−</button>
                                    <input type="number" id="qty_<?= $p['id'] ?>" value="1" min="1" max="<?= $stokBox ?>" readonly
                                        class="w-6 sm:w-8 text-center text-[11px] sm:text-xs font-bold bg-transparent text-[#0f2d5a] focus:outline-none select-none">
                                    <button type="button" onclick="adjustQty(<?= $p['id'] ?>, 1, <?= $stokBox ?>)"
                                        class="w-5 sm:w-7 h-full flex items-center justify-center hover:bg-slate-200/60 text-slate-700 font-bold transition text-xs sm:text-sm cursor-pointer">+</button>
                                </div>

                                <button type="button" onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars_decode($p['nama_produk'])) ?>', <?= $harga ?>, <?= intval($p['berat_gram']) ?>, '<?= addslashes($p['gambar'] ?? '') ?>', '<?= addslashes($satB) ?>', <?= $rasio ?>, <?= $stokBox ?>, document.getElementById('qty_<?= $p['id'] ?>').value)"
                                    class="flex-1 h-8 sm:h-9 px-2 sm:px-3 rounded-full bg-[#0f2d5a] hover:bg-[#1a75d2] text-white text-[10px] sm:text-[11px] font-bold uppercase tracking-wider transition cursor-pointer flex items-center justify-center gap-1">
                                    <span>+ Keranjang</span>
                                </button>

                                <button type="button" title="Beli Sekarang" onclick="buyNow(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars_decode($p['nama_produk'])) ?>', <?= $harga ?>, <?= intval($p['berat_gram']) ?>, '<?= addslashes($p['gambar'] ?? '') ?>', '<?= addslashes($satB) ?>', <?= $rasio ?>, <?= $stokBox ?>, document.getElementById('qty_<?= $p['id'] ?>').value)"
                                    class="h-8 sm:h-9 px-2.5 sm:px-3 rounded-full border border-[#0f2d5a] text-[#0f2d5a] hover:bg-[#eef6fb] text-[10px] sm:text-[11px] font-bold transition cursor-pointer shrink-0 hidden md:block">
                                    Beli
                                </button>
                            </div>
                            <?php else: ?>
                            <div class="w-full h-8 sm:h-9 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-[10px] sm:text-[11px] font-bold uppercase tracking-wider border border-slate-200">
                                Stok Habis
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (empty($products)): ?>
                    <div class="col-span-full py-20 text-center bg-[#f8fafc] border border-slate-200">
                        <h3 class="text-lg font-extrabold text-[#0f2d5a] mb-1">Produk Tidak Ditemukan</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto mb-5">
                            Tidak ada produk yang cocok dengan pencarian atau filter kategori yang Anda pilih.
                        </p>
                        <div class="flex justify-center gap-3">
                            <a href="kategori.php" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 text-xs font-bold uppercase tracking-wider hover:bg-slate-50 transition">Reset Filter</a>
                            <a href="index.php" class="px-5 py-2.5 bg-[#0f2d5a] text-white text-xs font-bold uppercase tracking-wider hover:bg-[#1a75d2] transition">Ke Beranda</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

            </div>

        </div>
    </div>
</main>

<!-- Mobile Filter Drawer (Slide-over from right) -->
<div id="mobile-filter-overlay" class="hidden fixed inset-0 bg-[#0f2d5a]/40 z-50 backdrop-blur-xs transition-opacity duration-300 opacity-0" onclick="toggleMobileFilter()"></div>
<div id="mobile-filter-drawer" class="fixed top-0 right-0 bottom-0 w-[85%] max-w-sm bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <!-- Drawer Header -->
    <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between bg-[#f8fafc]">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-[#1a75d2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            <h3 class="text-sm font-extrabold text-[#0f2d5a] uppercase tracking-wider">Filter &amp; Kategori</h3>
        </div>
        <button type="button" onclick="toggleMobileFilter()" class="p-1.5 text-slate-400 hover:text-[#0f2d5a] transition cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <!-- Drawer Body -->
    <div class="flex-1 overflow-y-auto p-5 space-y-6 text-xs">
        <!-- 1. Categories in Drawer -->
        <div>
            <h4 class="font-extrabold text-[#0f2d5a] uppercase tracking-wider mb-3 pb-1 border-b border-slate-200">Pilih Kategori</h4>
            <ul class="space-y-2 text-sm">
                <li>
                    <a href="kategori.php" class="flex items-center justify-between py-1 transition <?= (empty($kategori) && !$indukId) ? 'font-bold text-[#0f2d5a]' : 'text-slate-600 hover:text-[#0f2d5a]' ?>">
                        <span>Semua Koleksi</span>
                        <span class="text-xs text-slate-400">(<?= count($sidebarInduk['Obat']) + count($sidebarInduk['Alat Kesehatan']) ?>)</span>
                    </a>
                </li>
                <?php foreach (['Obat' => 'Obat & Farmasi', 'Alat Kesehatan' => 'Alat Kesehatan Medis'] as $catKey => $catTitle): ?>
                <li class="border-t border-slate-100 pt-2">
                    <a href="kategori.php?kategori=<?= urlencode($catKey) ?>" class="block py-1 <?= ($kategori === $catKey && !$indukId) ? 'font-bold text-[#0f2d5a]' : 'text-slate-700 hover:text-[#0f2d5a] font-medium' ?>">
                        <?= htmlspecialchars($catTitle) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- 2. Stock Filter in Drawer -->
        <div>
            <h4 class="font-extrabold text-[#0f2d5a] uppercase tracking-wider mb-3 pb-1 border-b border-slate-200">Ketersediaan Stok</h4>
            <div class="space-y-2 text-xs text-slate-600">
                <label class="flex items-center justify-between cursor-pointer hover:text-[#0f2d5a]">
                    <span class="flex items-center gap-2">
                        <input type="checkbox" id="chk_instock_m" onchange="syncMobileFilter(this, 'chk_instock')" class="w-3.5 h-3.5 accent-[#0f2d5a] rounded-none">
                        <span>Tersedia (Stok Ada)</span>
                    </span>
                    <span class="text-slate-400">(<?= $inStockCount ?>)</span>
                </label>
                <label class="flex items-center justify-between cursor-pointer hover:text-[#0f2d5a]">
                    <span class="flex items-center gap-2">
                        <input type="checkbox" id="chk_outstock_m" onchange="syncMobileFilter(this, 'chk_outstock')" class="w-3.5 h-3.5 accent-[#0f2d5a] rounded-none">
                        <span>Stok Habis</span>
                    </span>
                    <span class="text-slate-400">(<?= $outStockCount ?>)</span>
                </label>
            </div>
        </div>

        <!-- 3. Search in Drawer -->
        <div>
            <h4 class="font-extrabold text-[#0f2d5a] uppercase tracking-wider mb-3 pb-1 border-b border-slate-200">Pencarian</h4>
            <form action="kategori.php" method="GET" class="space-y-2">
                <?php if ($kategori): ?>
                    <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori) ?>">
                <?php endif; ?>
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Ketik nama obat / alkes..." class="w-full px-3 py-2 text-xs border border-slate-200 focus:outline-none focus:border-[#0f2d5a]">
                <button type="submit" class="w-full py-2 bg-[#0f2d5a] hover:bg-[#1a75d2] text-white text-xs font-bold uppercase tracking-wider transition">
                    Cari Produk
                </button>
            </form>
        </div>
    </div>

    <!-- Drawer Footer -->
    <div class="p-4 border-t border-slate-200 bg-[#f8fafc] pb-safe">
        <button type="button" onclick="toggleMobileFilter()" class="w-full py-3 bg-[#0f2d5a] hover:bg-[#1a75d2] text-white text-xs font-bold uppercase tracking-widest transition text-center">
            Terapkan &amp; Tutup
        </button>
    </div>
</div>

<script>
function toggleMobileFilter() {
    const overlay = document.getElementById('mobile-filter-overlay');
    const drawer = document.getElementById('mobile-filter-drawer');
    if (!drawer || !overlay) return;
    const isHidden = drawer.classList.contains('translate-x-full');

    if (isHidden) {
        overlay.classList.remove('hidden');
        setTimeout(() => overlay.classList.remove('opacity-0'), 10);
        drawer.classList.remove('translate-x-full');
        document.body.style.overflow = 'hidden';
    } else {
        overlay.classList.add('opacity-0');
        drawer.classList.add('translate-x-full');
        document.body.style.overflow = '';
        setTimeout(() => overlay.classList.add('hidden'), 300);
    }
}

function syncMobileFilter(el, targetId) {
    const target = document.getElementById(targetId);
    if (target) {
        target.checked = el.checked;
        applyClientFilters();
    }
}

function toggleSidebarCat(id, btn) {
    const el = document.getElementById(id);
    if (!el) return;
    const isHidden = el.classList.contains('hidden');
    if (isHidden) {
        el.classList.remove('hidden');
        btn.textContent = '−';
    } else {
        el.classList.add('hidden');
        btn.textContent = '+';
    }
}

function setGridColumns(cols) {
    const grid = document.getElementById('product-grid');
    const btn3 = document.getElementById('grid_btn_3');
    const btn4 = document.getElementById('grid_btn_4');
    if (!grid) return;

    if (cols === 4) {
        grid.classList.remove('md:grid-cols-3');
        grid.classList.add('md:grid-cols-3', 'xl:grid-cols-4');
        btn4 && btn4.classList.add('active');
        btn3 && btn3.classList.remove('active');
    } else {
        grid.classList.remove('xl:grid-cols-4');
        grid.classList.add('md:grid-cols-3');
        btn3 && btn3.classList.add('active');
        btn4 && btn4.classList.remove('active');
    }
}

function resetAvailabilityFilter() {
    const cIn = document.getElementById('chk_instock');
    const cOut = document.getElementById('chk_outstock');
    if (cIn) cIn.checked = false;
    if (cOut) cOut.checked = false;
    applyClientFilters();
}

function applyClientFilters() {
    const cIn  = document.getElementById('chk_instock')?.checked;
    const cOut = document.getElementById('chk_outstock')?.checked;
    const lbl  = document.getElementById('avail_selected_lbl');
    const selCount = (cIn ? 1 : 0) + (cOut ? 1 : 0);
    if (lbl) lbl.textContent = selCount + ' dipilih';

    const grid = document.getElementById('product-grid');
    const cards = Array.from(grid.querySelectorAll('.prod-card'));
    let visible = 0;

    cards.forEach(c => {
        const stok = parseInt(c.dataset.stok || '0', 10);
        let show = true;
        if (cIn && !cOut) show = stok > 0;
        else if (!cIn && cOut) show = stok <= 0;

        c.style.display = show ? 'flex' : 'none';
        if (show) visible++;
    });

    const vCount = document.getElementById('visible_count');
    if (vCount) vCount.textContent = visible;
}

function sortProducts() {
    const val  = document.getElementById('sort_select').value;
    const grid = document.getElementById('product-grid');
    const cards = Array.from(grid.querySelectorAll('.prod-card'));
    
    cards.sort((a, b) => {
        if (val === 'price_asc')  return +a.dataset.price - +b.dataset.price;
        if (val === 'price_desc') return +b.dataset.price - +a.dataset.price;
        if (val === 'name_asc')   return a.dataset.name.localeCompare(b.dataset.name);
        if (val === 'stok_desc')  return +b.dataset.stok - +a.dataset.stok;
        return +a.dataset.id - +b.dataset.id;
    });
    
    grid.style.opacity = '0';
    setTimeout(() => {
        cards.forEach(c => grid.appendChild(c));
        grid.style.opacity = '1';
    }, 150);
}
document.getElementById('product-grid').style.transition = 'opacity 0.15s ease';
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
