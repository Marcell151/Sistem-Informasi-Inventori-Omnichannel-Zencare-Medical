<?php
// File: ecommerce/index.php — ZenCare Medical Store (Redesign v2)
// Toko Online Alat Kesehatan & Obat | Tampilan Modern & Responsif
$pageTitle = "Toko Alat Kesehatan & Obat";
require_once __DIR__ . '/header.php';

// Fetch products
$stmtProduk = $pdo->prepare("
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
           v.tampil_di_online, v.satuan_besar, v.satuan_kecil, v.rasio_konversi
    FROM produk_variasi v
    JOIN produk_induk i ON v.id_produk_induk = i.id
    LEFT JOIN stok_cabang sc ON sc.id_variasi = v.id AND sc.id_cabang = ?
    WHERE v.is_active = 1 AND i.is_active = 1 AND v.tampil_di_online = 1
    ORDER BY i.kategori ASC, i.nama_produk ASC, v.id ASC
");
$stmtProduk->execute([$activeCabangId]);
$products = $stmtProduk->fetchAll();

// Unique categories
$catCounts = [];
foreach ($products as $p) {
    $catCounts[$p['kategori']] = ($catCounts[$p['kategori']] ?? 0) + 1;
}
ksort($catCounts);

// Product images for slideshow (first 5 with images)
$slideProducts = array_filter($products, fn($p) => !empty($p['gambar']));
$slideProducts = array_values(array_slice($slideProducts, 0, 5));
if (empty($slideProducts)) $slideProducts = array_values(array_slice($products, 0, 3));
?>

<!-- Custom styles -->
<style>
/* HERO SLIDESHOW */
.hero-slide { display: none; opacity: 0; transition: opacity .5s ease; }
.hero-slide.active { display: flex; opacity: 1; }
.slide-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,.4); cursor: pointer; transition: all .2s; }
.slide-dot.active { background: white; width: 24px; border-radius: 4px; }

/* PRODUCT CARD */
.prod-card { transition: transform .2s, box-shadow .2s; }
.prod-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(26,117,210,.13); }

/* CATEGORY CARDS */
.cat-card { transition: transform .15s, box-shadow .15s; }
.cat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(26,117,210,.1); }
</style>

<main class="flex-1 w-full">

    <!-- ============================================================ -->
    <!-- HERO BANNER SLIDESHOW (gambar produk)                       -->
    <!-- ============================================================ -->
    <section class="relative overflow-hidden bg-gradient-to-br from-[#0f2d5a] to-[#1a75d2]" style="min-height: 320px;">

        <!-- Slide Item(s) -->
        <?php foreach ($slideProducts as $si => $sp): ?>
        <div class="hero-slide absolute inset-0 flex flex-col md:flex-row items-center justify-between gap-0 px-6 sm:px-10 lg:px-16 py-10 <?= $si === 0 ? 'active' : '' ?>" data-slide="<?= $si ?>">
            <!-- Text Side -->
            <div class="flex-1 text-white z-10 max-w-lg mb-6 md:mb-0">
                <span class="inline-block px-3 py-1 rounded-full border border-white/25 bg-white/10 text-xs font-bold uppercase tracking-widest text-blue-100 mb-4">
                    <?= htmlspecialchars($sp['kategori']) ?>
                </span>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold leading-tight tracking-tight mb-3">
                    <?= htmlspecialchars($sp['nama_induk']) ?>
                </h1>
                <p class="text-blue-100 text-sm leading-relaxed mb-6 line-clamp-2">
                    <?= htmlspecialchars(mb_strimwidth($sp['deskripsi'] ?? 'Produk medis berkualitas tinggi dengan jaminan mutu dan izin resmi.', 0, 110, '…')) ?>
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="produk.php?id=<?= $sp['id'] ?>" class="px-5 py-3 bg-white text-[#0f2d5a] font-bold text-sm rounded-xl hover:bg-blue-50 transition shadow-md">
                        Lihat Detail Produk
                    </a>
                    <a href="#katalog" class="px-5 py-3 bg-white/15 border border-white/30 text-white font-semibold text-sm rounded-xl hover:bg-white/25 transition">
                        Lihat Semua Produk
                    </a>
                </div>
            </div>

            <!-- Image Side -->
            <div class="shrink-0 flex items-center justify-center md:w-60 lg:w-72">
                <?php if (!empty($sp['gambar'])): ?>
                    <img src="../<?= htmlspecialchars($sp['gambar']) ?>" alt="<?= htmlspecialchars($sp['nama_induk']) ?>"
                         class="h-44 sm:h-52 lg:h-64 object-contain drop-shadow-2xl"
                         onerror="this.onerror=null; this.src='<?= htmlspecialchars($sp['gambar']) ?>';">
                <?php else: ?>
                    <div class="w-48 h-48 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center">
                        <svg class="w-20 h-20 text-white/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Slide dots & controls -->
        <?php if (count($slideProducts) > 1): ?>
        <div class="absolute bottom-5 left-0 right-0 flex items-center justify-center gap-2 z-20">
            <?php foreach ($slideProducts as $si => $sp): ?>
                <span class="slide-dot <?= $si === 0 ? 'active' : '' ?>" onclick="goSlide(<?= $si ?>)"></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Prev / Next arrows -->
        <?php if (count($slideProducts) > 1): ?>
        <button onclick="prevSlide()" class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-9 h-9 bg-white/15 hover:bg-white/30 border border-white/25 rounded-full flex items-center justify-center text-white transition">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <button onclick="nextSlide()" class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-9 h-9 bg-white/15 hover:bg-white/30 border border-white/25 rounded-full flex items-center justify-center text-white transition">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        <?php endif; ?>
    </section>

    <!-- ============================================================ -->
    <!-- TRUST BADGES ROW (simplified, no API names)                 -->
    <!-- ============================================================ -->
    <div class="bg-white border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 py-4 grid grid-cols-2 md:grid-cols-4 gap-3">
            <?php $badges = [
                ['icon'=>'<path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>', 'label'=>'Produk Bergaransi'],
                ['icon'=>'<path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>', 'label'=>'Pengiriman Seluruh Indonesia'],
                ['icon'=>'<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>', 'label'=>'Izin Edar Resmi Kemenkes'],
                ['icon'=>'<path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>', 'label'=>'Pembayaran Aman & Terpercaya'],
            ]; ?>
            <?php foreach ($badges as $b): ?>
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-lg bg-zcLt text-zc flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><?= $b['icon'] ?></svg>
                </div>
                <span class="text-sm font-semibold text-zcTxt leading-tight"><?= $b['label'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-8">

        <!-- ============================================================ -->
        <!-- CATEGORY GRID SHOWCASE                                       -->
        <!-- ============================================================ -->
        <section class="mb-10">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-xl font-extrabold text-zcTxt">Belanja per Kategori</h2>
                    <p class="text-sm text-zcMut mt-0.5">Temukan produk yang Anda butuhkan berdasarkan kategori</p>
                </div>
                <a href="kategori.php" class="text-sm font-bold text-zc hover:underline flex items-center gap-1">
                    Semua Kategori
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                <?php foreach ($catCounts as $catName => $count): ?>
                <a href="kategori.php?kategori=<?= urlencode($catName) ?>"
                   class="cat-card bg-white border border-slate-200 rounded-2xl p-4 flex flex-col items-center text-center group hover:border-zc">
                    <div class="w-12 h-12 rounded-xl bg-zcLt text-zc group-hover:bg-zc group-hover:text-white flex items-center justify-center mb-3 transition-all">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-zcTxt group-hover:text-zc line-clamp-2 leading-tight mb-1">
                        <?= htmlspecialchars($catName) ?>
                    </span>
                    <span class="text-xs text-zcMut"><?= $count ?> Produk</span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- PRODUCT CATALOG                                              -->
        <!-- ============================================================ -->
        <section id="katalog">
            <!-- Header Row -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-200">
                <div>
                    <div class="flex items-center gap-2.5 mb-1">
                        <h2 class="text-xl font-extrabold text-zcTxt">Semua Produk</h2>
                        <span class="px-2.5 py-0.5 bg-zcLt text-zc font-extrabold text-xs rounded-full"><?= count($products) ?></span>
                    </div>
                    <p class="text-sm text-zcMut">Klik foto atau nama produk untuk melihat detail dan beli.</p>
                </div>
                <select id="sort_select" onchange="sortProducts()" class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white focus:outline-none focus:border-zc text-zcTxt font-medium w-full sm:w-auto">
                    <option value="default">Urutan Standar</option>
                    <option value="price_asc">Harga Terendah</option>
                    <option value="price_desc">Harga Tertinggi</option>
                    <option value="name_asc">Nama Produk (A–Z)</option>
                </select>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5" id="product-grid">
                <?php foreach ($products as $p):
                    $stokPcs     = intval($p['stok_sistem']);
                    $rasio       = intval($p['rasio_konversi']) ?: 1;
                    $stokBox     = floor($stokPcs / $rasio);
                    $harga       = floatval($p['harga_jual']);
                    $hargaEceran = floatval($p['harga_eceran']);
                    $satBesar    = $p['satuan_besar'] ?: 'Box';
                    $satKecil    = $p['satuan_kecil'] ?: 'Pcs';

                    if ($stokBox <= 0)       { $stokClass = 'text-rose-600'; $stokDot = 'bg-rose-500'; $stokLabel = 'Habis'; }
                    elseif ($stokBox <= 5)   { $stokClass = 'text-amber-700'; $stokDot = 'bg-amber-400'; $stokLabel = "Sisa $stokBox $satBesar"; }
                    else                     { $stokClass = 'text-emerald-700'; $stokDot = 'bg-emerald-500'; $stokLabel = "$stokBox $satBesar"; }

                    $desc = trim($p['deskripsi'] ?? '');
                    $descShort = $desc ? mb_strimwidth($desc, 0, 80, '…') : 'Produk medis berkualitas dengan jaminan mutu resmi.';
                ?>
                <!-- PRODUCT CARD -->
                <div class="prod-card group bg-white rounded-2xl overflow-hidden flex flex-col border border-slate-200"
                     data-name="<?= strtolower(htmlspecialchars($p['nama_produk'])) ?>"
                     data-price="<?= $harga ?>"
                     data-id="<?= $p['id'] ?>">

                    <!-- Image Zone -->
                    <a href="produk.php?id=<?= $p['id'] ?>" class="relative block bg-slate-50 overflow-hidden" style="height:200px;">
                        <?php if (!empty($p['gambar'])): ?>
                            <img src="../<?= htmlspecialchars($p['gambar']) ?>"
                                 alt="<?= htmlspecialchars($p['nama_produk']) ?>"
                                 class="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-300"
                                 onerror="this.onerror=null; this.src='<?= htmlspecialchars($p['gambar']) ?>';">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                        <?php endif; ?>
                        <!-- Category tag -->
                        <span class="absolute top-3 left-3 px-2.5 py-1 bg-white/95 text-zc text-xs font-bold rounded-lg border border-zc/20">
                            <?= htmlspecialchars($p['kategori']) ?>
                        </span>
                        <!-- Stock dot -->
                        <span class="absolute top-3 right-3 flex items-center gap-1.5 px-2.5 py-1 bg-white/95 text-xs font-semibold rounded-lg <?= $stokClass ?>">
                            <span class="w-2 h-2 rounded-full <?= $stokDot ?> shrink-0"></span>
                            <?= htmlspecialchars($stokLabel) ?>
                        </span>
                    </a>

                    <!-- Card Body -->
                    <div class="p-4 flex flex-col flex-1">

                        <!-- Name -->
                        <a href="produk.php?id=<?= $p['id'] ?>" class="block mb-3">
                            <h3 class="text-base font-bold text-zcTxt leading-snug line-clamp-2 group-hover:text-zc transition-colors">
                                <?= htmlspecialchars($p['nama_induk']) ?>
                                <span class="font-normal text-zcMut text-sm"> &mdash; <?= htmlspecialchars($p['nama_variasi']) ?></span>
                            </h3>
                        </a>

                        <!-- Specs Mini-Sheet -->
                        <div class="rounded-xl bg-slate-50 border border-slate-100 divide-y divide-slate-100 mb-4 text-sm">
                            <div class="flex gap-2 px-3 py-2">
                                <span class="text-zcMut w-20 shrink-0">Kategori</span>
                                <span class="font-semibold text-zcTxt truncate"><?= htmlspecialchars($p['kategori']) ?></span>
                            </div>
                            <div class="flex gap-2 px-3 py-2">
                                <span class="text-zcMut w-20 shrink-0">Keterangan</span>
                                <span class="font-medium text-zcMut line-clamp-2 leading-snug"><?= htmlspecialchars($descShort) ?></span>
                            </div>
                            <div class="flex gap-2 px-3 py-2">
                                <span class="text-zcMut w-20 shrink-0">Kemasan</span>
                                <span class="font-bold text-zcTxt">1 <?= htmlspecialchars($satBesar) ?> = <?= $rasio ?> <?= htmlspecialchars($satKecil) ?></span>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="mb-4">
                            <div class="flex items-baseline gap-2">
                                <span class="text-xl font-extrabold text-zcTxt">Rp <?= number_format($harga, 0, ',', '.') ?></span>
                                <span class="text-sm text-zcMut font-medium">/ <?= htmlspecialchars($satBesar) ?></span>
                            </div>
                            <?php if ($hargaEceran > 0): ?>
                            <div class="text-xs text-slate-400 mt-0.5">
                                ~Rp <?= number_format($hargaEceran, 0, ',', '.') ?> / <?= htmlspecialchars($satKecil) ?> (satuan terkecil)
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- CTA -->
                        <div class="mt-auto">
                        <?php if ($stokBox >= 1): ?>
                            <div class="flex items-center gap-2 mb-2.5">
                                <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl overflow-hidden flex-1">
                                    <button type="button" onclick="adjustQty(<?= $p['id'] ?>, -1, <?= $stokBox ?>)"
                                        class="w-9 h-9 flex items-center justify-center hover:bg-slate-200 text-zcTxt font-bold border-r border-slate-200 cursor-pointer transition text-lg">
                                        &minus;
                                    </button>
                                    <input type="number" id="qty_<?= $p['id'] ?>" value="1" min="1" max="<?= $stokBox ?>" readonly
                                        class="flex-1 text-center text-sm font-bold py-2 bg-transparent focus:outline-none select-none">
                                    <button type="button" onclick="adjustQty(<?= $p['id'] ?>, 1, <?= $stokBox ?>)"
                                        class="w-9 h-9 flex items-center justify-center hover:bg-slate-200 text-zcTxt font-bold border-l border-slate-200 cursor-pointer transition text-lg">
                                        +
                                    </button>
                                </div>
                                <span class="text-xs text-slate-500 shrink-0">Max: <?= $stokBox ?></span>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <button onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes($p['nama_produk']) ?>', <?= $harga ?>, <?= intval($p['berat_gram']) ?>, '<?= addslashes($p['gambar'] ?? '') ?>', '<?= addslashes($satBesar) ?>', <?= $rasio ?>, <?= $stokBox ?>, document.getElementById('qty_<?= $p['id'] ?>').value)"
                                    class="flex items-center justify-center gap-1.5 text-sm font-bold py-2.5 px-3 rounded-xl bg-zcLt hover:bg-blue-100 text-zc border border-zc/25 cursor-pointer active:scale-95 transition">
                                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.55L23 6H6"/></svg>
                                    Keranjang
                                </button>
                                <button onclick="buyNow(<?= $p['id'] ?>, '<?= addslashes($p['nama_produk']) ?>', <?= $harga ?>, <?= intval($p['berat_gram']) ?>, '<?= addslashes($p['gambar'] ?? '') ?>', '<?= addslashes($satBesar) ?>', <?= $rasio ?>, <?= $stokBox ?>, document.getElementById('qty_<?= $p['id'] ?>').value)"
                                    class="flex items-center justify-center gap-1 text-sm font-bold py-2.5 px-3 rounded-xl bg-zc hover:bg-zcHv text-white cursor-pointer active:scale-95 transition shadow-sm">
                                    Beli Sekarang
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="w-full py-3 px-4 rounded-xl bg-slate-100 text-slate-400 text-center text-sm font-semibold cursor-not-allowed border border-slate-200">
                                Stok Habis
                            </div>
                        <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Empty State -->
                <?php if (empty($products)): ?>
                <div class="col-span-full py-16 text-center">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <p class="text-base font-semibold text-zcMut">Belum ada produk tersedia.</p>
                </div>
                <?php endif; ?>
            </div>
        </section>

    </div><!-- /max-w-7xl -->
</main>

<script>
// ── Slideshow ──
let curSlide = 0;
const slides = document.querySelectorAll('.hero-slide');
const dots   = document.querySelectorAll('.slide-dot');
let slideTimer = null;

function goSlide(idx) {
    slides[curSlide].classList.remove('active');
    dots[curSlide] && dots[curSlide].classList.remove('active');
    curSlide = (idx + slides.length) % slides.length;
    slides[curSlide].classList.add('active');
    dots[curSlide] && dots[curSlide].classList.add('active');
}
function nextSlide() { goSlide(curSlide + 1); restartTimer(); }
function prevSlide() { goSlide(curSlide - 1); restartTimer(); }
function restartTimer() { clearInterval(slideTimer); slideTimer = setInterval(() => goSlide(curSlide + 1), 4000); }

if (slides.length > 1) restartTimer();

// ── Sorting ──
function sortProducts() {
    const val  = document.getElementById('sort_select').value;
    const grid = document.getElementById('product-grid');
    const cards = Array.from(grid.querySelectorAll('.prod-card'));

    cards.sort((a, b) => {
        if (val === 'price_asc')  return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
        if (val === 'price_desc') return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
        if (val === 'name_asc')   return a.dataset.name.localeCompare(b.dataset.name);
        return parseInt(a.dataset.id) - parseInt(b.dataset.id);
    });
    cards.forEach(c => grid.appendChild(c));
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
