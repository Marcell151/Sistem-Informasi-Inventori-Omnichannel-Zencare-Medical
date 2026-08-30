<?php
// File: ecommerce/kategori.php
// Category Directory & Category-Specific Catalog for ZenCare E-Commerce
$selectedCategory = trim($_GET['kategori'] ?? '');
$searchQuery      = trim($_GET['q'] ?? '');
$sortBy           = trim($_GET['sort'] ?? 'default');

$pageTitle = $selectedCategory ? "Kategori: " . htmlspecialchars($selectedCategory) : "Semua Kategori Produk Medis";
require_once __DIR__ . '/header.php';

// Fetch all available categories with active item count
$stmtAllCats = $pdo->prepare("
    SELECT i.kategori, COUNT(v.id) AS total_produk
    FROM produk_variasi v
    JOIN produk_induk i ON v.id_produk_induk = i.id
    WHERE v.is_active = 1 AND i.is_active = 1 AND v.tampil_di_online = 1
    GROUP BY i.kategori
    ORDER BY i.kategori ASC
");
$stmtAllCats->execute();
$allCategories = $stmtAllCats->fetchAll();

// Build products query based on filter
$sql = "
    SELECT v.id,
           i.nama_produk AS nama_induk,
           v.nama_variasi,
           CONCAT(i.nama_produk, ' - ', v.nama_variasi) AS nama_produk,
           i.kategori,
           v.harga_jual_besar AS harga_jual,
           v.harga_jual_kecil AS harga_eceran,
           v.berat AS berat_gram,
           v.gambar,
           v.sku_variasi,
           i.deskripsi,
           COALESCE(sc.stok, 0) AS stok_sistem,
           v.satuan_besar, v.satuan_kecil, v.rasio_konversi
    FROM produk_variasi v
    JOIN produk_induk i ON v.id_produk_induk = i.id
    LEFT JOIN stok_cabang sc ON sc.id_variasi = v.id AND sc.id_cabang = ?
    WHERE v.is_active = 1 AND i.is_active = 1 AND v.tampil_di_online = 1
";
$params = [$activeCabangId];

if ($selectedCategory && $selectedCategory !== 'semua') {
    $sql .= " AND i.kategori = ?";
    $params[] = $selectedCategory;
}

if ($searchQuery) {
    $sql .= " AND (i.nama_produk LIKE ? OR v.nama_variasi LIKE ? OR v.sku_variasi LIKE ? OR i.deskripsi LIKE ?)";
    $term = "%$searchQuery%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

// Order clause
if ($sortBy === 'price_asc') {
    $sql .= " ORDER BY v.harga_jual_besar ASC";
} elseif ($sortBy === 'price_desc') {
    $sql .= " ORDER BY v.harga_jual_besar DESC";
} elseif ($sortBy === 'name_asc') {
    $sql .= " ORDER BY i.nama_produk ASC, v.nama_variasi ASC";
} else {
    $sql .= " ORDER BY i.kategori ASC, i.nama_produk ASC, v.id ASC";
}

$stmtFiltered = $pdo->prepare($sql);
$stmtFiltered->execute($params);
$products = $stmtFiltered->fetchAll();
?>

<main class="max-w-7xl mx-auto px-4 lg:px-8 py-6 flex-1 w-full">

    <!-- ── Breadcrumb ── -->
    <nav class="flex items-center gap-2 text-xs text-zcMut mb-5">
        <a href="index.php" class="hover:text-zc transition">Beranda</a>
        <span>/</span>
        <a href="kategori.php" class="<?= !$selectedCategory ? 'text-zc font-bold' : 'hover:text-zc transition' ?>">Semua Kategori</a>
        <?php if ($selectedCategory): ?>
            <span>/</span>
            <span class="text-zcTxt font-bold"><?= htmlspecialchars($selectedCategory) ?></span>
        <?php endif; ?>
    </nav>

    <!-- ── Category Header Banner ── -->
    <div class="bg-white border border-slate-100 rounded-3xl p-6 sm:p-8 shadow-xs mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-zcLt border border-zc/20 rounded-full text-xs font-extrabold text-zc uppercase tracking-wider mb-2">
                <?= $selectedCategory ? 'Kategori Spesifik' : 'Direktori Medis' ?>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-zcTxt leading-tight">
                <?= $selectedCategory ? htmlspecialchars($selectedCategory) : 'Katalog Seluruh Kategori Medis' ?>
            </h1>
            <p class="text-xs text-zcMut mt-1">
                Menampilkan <strong class="text-zcTxt"><?= count($products) ?></strong> jenis produk tersedia &bull; Lokasi Cabang: <strong class="text-zcTxt"><?= htmlspecialchars($cabangAktif['nama'] ?? '') ?></strong>
            </p>
        </div>

        <!-- Category Pills Navigation -->
        <div class="flex flex-wrap gap-1.5 max-w-lg">
            <a href="kategori.php" class="px-3 py-1.5 rounded-xl text-xs font-bold border transition-all <?= !$selectedCategory ? 'bg-zc text-white border-zc shadow-xs' : 'bg-slate-50 text-zcTxt border-slate-200 hover:bg-slate-100' ?>">
                Semua (<?= count($allCategories) ?>)
            </a>
            <?php foreach ($allCategories as $cat): 
                $isActive = ($selectedCategory === $cat['kategori']);
            ?>
            <a href="kategori.php?kategori=<?= urlencode($cat['kategori']) ?>" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold border transition-all <?= $isActive ? 'bg-zc text-white border-zc shadow-xs' : 'bg-slate-50 text-zcTxt border-slate-200 hover:bg-slate-100' ?>">
                <?= htmlspecialchars($cat['kategori']) ?>
                <span class="text-[10px] opacity-75 font-normal ml-0.5">(<?= $cat['total_produk'] ?>)</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── Filter & Search Bar Row ── -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-6">
        <form method="GET" class="flex items-center gap-2 w-full sm:w-auto">
            <?php if ($selectedCategory): ?>
                <input type="hidden" name="kategori" value="<?= htmlspecialchars($selectedCategory) ?>">
            <?php endif; ?>
            <div class="relative flex-1 sm:w-72">
                <svg class="w-4 h-4 text-zcMut absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Cari di kategori ini..."
                       class="w-full text-xs pl-9 pr-3 py-2 border border-slate-200 rounded-xl bg-white focus:outline-none focus:border-zc shadow-xs">
            </div>
            <button type="submit" class="px-3.5 py-2 bg-zc hover:bg-zcHv text-white font-bold text-xs rounded-xl shadow-xs cursor-pointer">
                Cari
            </button>
            <?php if ($searchQuery): ?>
                <a href="kategori.php<?= $selectedCategory ? '?kategori=' . urlencode($selectedCategory) : '' ?>" class="text-xs text-rose-500 hover:underline">
                    Reset
                </a>
            <?php endif; ?>
        </form>

        <form method="GET" class="flex items-center gap-2 self-end sm:self-auto">
            <?php if ($selectedCategory): ?><input type="hidden" name="kategori" value="<?= htmlspecialchars($selectedCategory) ?>"><?php endif; ?>
            <?php if ($searchQuery): ?><input type="hidden" name="q" value="<?= htmlspecialchars($searchQuery) ?>"><?php endif; ?>
            <span class="text-xs text-zcMut">Urutkan:</span>
            <select name="sort" onchange="this.form.submit()" class="text-xs border border-slate-200 rounded-xl px-3 py-2 bg-white focus:outline-none focus:border-zc font-semibold text-zcTxt shadow-xs">
                <option value="default" <?= $sortBy === 'default' ? 'selected' : '' ?>>Standar</option>
                <option value="price_asc" <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>Harga Terendah</option>
                <option value="price_desc" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>Harga Tertinggi</option>
                <option value="name_asc" <?= $sortBy === 'name_asc' ? 'selected' : '' ?>>Nama (A-Z)</option>
            </select>
        </form>
    </div>

    <!-- ── Product Grid ── -->
    <?php if (!empty($products)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5" id="product-grid">
            <?php foreach ($products as $p):
                $stokPcs     = intval($p['stok_sistem']);
                $rasio       = intval($p['rasio_konversi']) ?: 1;
                $stokBox     = floor($stokPcs / $rasio);
                $harga       = floatval($p['harga_jual']);
                $hargaEceran = floatval($p['harga_eceran']);
                $satBesar    = $p['satuan_besar'] ?: 'Box';
                $satKecil    = $p['satuan_kecil'] ?: 'Pcs';

                if ($stokBox <= 0) {
                    $stokClass = 'bg-rose-50 text-rose-600 border-rose-200';
                    $stokDot   = 'bg-rose-500';
                    $stokLabel = 'Habis';
                } elseif ($stokBox <= 5) {
                    $stokClass = 'bg-amber-50 text-amber-700 border-amber-200';
                    $stokDot   = 'bg-amber-400';
                    $stokLabel = "Sisa $stokBox $satBesar";
                } else {
                    $stokClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                    $stokDot   = 'bg-emerald-500';
                    $stokLabel = "$stokBox $satBesar Tersedia";
                }

                $desc = trim($p['deskripsi'] ?? '');
                $descShort = $desc ? mb_strimwidth($desc, 0, 72, '…') : 'Standar alat medis bersertifikasi resmi.';
                $sku = $p['sku_variasi'] ?? '';
            ?>
            <div class="product-card group bg-white rounded-2xl overflow-hidden flex flex-col transition-all duration-200 border border-slate-100 hover:border-zc/30 hover:shadow-lg"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.04);">

                <!-- Image -->
                <a href="produk.php?id=<?= $p['id'] ?>" class="relative bg-gradient-to-br from-slate-50 via-blue-50/20 to-slate-100 overflow-hidden block" style="height: 180px;">
                    <?php if (!empty($p['gambar'])): ?>
                        <img src="../<?= htmlspecialchars($p['gambar']) ?>"
                             alt="<?= htmlspecialchars($p['nama_produk']) ?>"
                             class="w-full h-full object-contain p-3 transition-transform duration-300 group-hover:scale-105"
                             onerror="this.onerror=null; this.src='<?= htmlspecialchars($p['gambar']) ?>';">
                    <?php else: ?>
                        <div class="w-full h-full flex flex-col items-center justify-center gap-2 text-slate-300">
                            <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                            <span class="text-[10px] font-medium text-slate-400">ZenCare Medical</span>
                        </div>
                    <?php endif; ?>

                    <div class="absolute top-2.5 left-2.5">
                        <span class="px-2 py-0.5 bg-white/95 backdrop-blur-sm text-zc text-[9px] font-extrabold rounded-md border border-zc/20 uppercase tracking-wider shadow-xs">
                            <?= htmlspecialchars($p['kategori']) ?>
                        </span>
                    </div>

                    <div class="absolute top-2.5 right-2.5">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md border text-[9px] font-bold <?= $stokClass ?> backdrop-blur-sm bg-white/90 shadow-xs">
                            <span class="w-1.5 h-1.5 rounded-full <?= $stokDot ?> shrink-0"></span>
                            <?= htmlspecialchars($stokLabel) ?>
                        </span>
                    </div>

                    <?php if ($sku): ?>
                    <div class="absolute bottom-2 left-2.5">
                        <span class="px-1.5 py-0.5 bg-slate-900/70 text-white text-[9px] font-mono rounded tracking-wide">
                            <?= htmlspecialchars($sku) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </a>

                <!-- Body -->
                <div class="p-4 flex flex-col flex-1">
                    <a href="produk.php?id=<?= $p['id'] ?>" class="block mb-2 group-hover:text-zc transition-colors">
                        <h3 class="text-[13px] font-bold text-zcTxt leading-snug line-clamp-2">
                            <?= htmlspecialchars($p['nama_induk']) ?>
                            <span class="font-normal text-zcMut"> &mdash; <?= htmlspecialchars($p['nama_variasi']) ?></span>
                        </h3>
                    </a>

                    <!-- Mini Specs-Sheet -->
                    <div class="mb-3 rounded-xl border border-slate-100 bg-slate-50/80 divide-y divide-slate-100 overflow-hidden text-[10px]">
                        <div class="flex items-center gap-2 px-2.5 py-1.5">
                            <span class="text-slate-400 w-16 shrink-0 font-medium">Kategori</span>
                            <span class="font-semibold text-zcTxt truncate"><?= htmlspecialchars($p['kategori']) ?></span>
                        </div>
                        <div class="flex items-start gap-2 px-2.5 py-1.5">
                            <span class="text-slate-400 w-16 shrink-0 font-medium">Spesifikasi</span>
                            <span class="font-medium text-zcMut leading-relaxed line-clamp-2"><?= htmlspecialchars($descShort) ?></span>
                        </div>
                        <div class="flex items-center gap-2 px-2.5 py-1.5">
                            <span class="text-slate-400 w-16 shrink-0 font-medium">Kemasan</span>
                            <span class="font-bold text-zcTxt">1 <?= htmlspecialchars($satBesar) ?> = <?= $rasio ?> <?= htmlspecialchars($satKecil) ?></span>
                        </div>
                    </div>

                    <!-- Price Block -->
                    <div class="mb-3">
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-[17px] font-extrabold text-zcTxt tracking-tight">
                                Rp <?= number_format($harga, 0, ',', '.') ?>
                            </span>
                            <span class="text-[11px] text-zcMut font-semibold">/ <?= htmlspecialchars($satBesar) ?></span>
                        </div>
                        <?php if ($hargaEceran > 0): ?>
                        <div class="flex items-center gap-1.5 mt-0.5 text-[10px] text-zcMut">
                            <span>~Rp <?= number_format($hargaEceran, 0, ',', '.') ?></span>
                            <span class="text-slate-400 font-normal">/ <?= htmlspecialchars($satKecil) ?> (ref. eceran)</span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div class="mt-auto pt-1">
                    <?php if ($stokBox >= 1): ?>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="flex items-center bg-slate-50 border border-slate-200 rounded-lg overflow-hidden flex-1 shadow-xs">
                                <button type="button" onclick="adjustQty(<?= $p['id'] ?>, -1, <?= $stokBox ?>)"
                                    class="w-7 h-7 flex items-center justify-center hover:bg-slate-200 text-zcTxt font-bold text-xs transition border-r border-slate-200 cursor-pointer">
                                    &minus;
                                </button>
                                <input type="number" id="qty_<?= $p['id'] ?>" value="1" min="1" max="<?= $stokBox ?>" readonly
                                    class="flex-1 text-center text-xs font-bold py-1 bg-transparent focus:outline-none select-none">
                                <button type="button" onclick="adjustQty(<?= $p['id'] ?>, 1, <?= $stokBox ?>)"
                                    class="w-7 h-7 flex items-center justify-center hover:bg-slate-200 text-zcTxt font-bold text-xs transition border-l border-slate-200 cursor-pointer">
                                    +
                                </button>
                            </div>
                            <span class="text-[9px] text-slate-400 shrink-0 font-medium">Max: <?= $stokBox ?></span>
                        </div>

                        <div class="grid grid-cols-2 gap-1.5">
                            <button onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes($p['nama_produk']) ?>', <?= $harga ?>, <?= intval($p['berat_gram']) ?>, '<?= addslashes($p['gambar'] ?? '') ?>', '<?= addslashes($satBesar) ?>', <?= $rasio ?>, <?= $stokBox ?>, document.getElementById('qty_<?= $p['id'] ?>').value)"
                                class="w-full flex items-center justify-center gap-1 text-[11px] font-bold py-2 px-2 rounded-xl transition bg-zcLt hover:bg-blue-100 text-zc border border-zc/25 cursor-pointer active:scale-95">
                                Keranjang
                            </button>
                            <button onclick="buyNow(<?= $p['id'] ?>, '<?= addslashes($p['nama_produk']) ?>', <?= $harga ?>, <?= intval($p['berat_gram']) ?>, '<?= addslashes($p['gambar'] ?? '') ?>', '<?= addslashes($satBesar) ?>', <?= $rasio ?>, <?= $stokBox ?>, document.getElementById('qty_<?= $p['id'] ?>').value)"
                                class="w-full flex items-center justify-center gap-1 text-[11px] font-bold py-2 px-2 rounded-xl transition bg-zc hover:bg-zcHv text-white shadow-xs cursor-pointer active:scale-95">
                                Beli Langsung
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="w-full py-2.5 px-3 rounded-xl bg-slate-100 text-slate-400 border border-slate-200 text-center text-xs font-semibold select-none cursor-not-allowed">
                            Stok Tidak Tersedia
                        </div>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="py-16 text-center bg-white rounded-3xl border border-slate-100 shadow-xs">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <h3 class="text-sm font-bold text-zcTxt">Tidak Ada Produk Ditemukan</h3>
            <p class="text-xs text-zcMut mt-1 mb-4">Coba cari dengan kata kunci lain atau pilih kategori yang berbeda.</p>
            <a href="kategori.php" class="inline-block px-4 py-2 bg-zcLt text-zc hover:bg-blue-100 text-xs font-bold rounded-xl transition">
                Reset ke Semua Kategori
            </a>
        </div>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/footer.php'; ?>
