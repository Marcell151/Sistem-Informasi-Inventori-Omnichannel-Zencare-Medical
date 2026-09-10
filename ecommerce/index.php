<?php
// File: ecommerce/index.php — ZenCare Medical Store (Redesign v3, Pharmify-inspired)
$pageTitle = "Toko Alat Kesehatan & Obat";
require_once __DIR__ . '/header.php';

// Fetch all active products
$stmtProduk = $pdo->prepare("
    SELECT v.id,
           i.nama_produk AS nama_induk,
           v.nama_variasi,
           CONCAT(i.nama_produk, ' — ', v.nama_variasi) AS nama_produk,
           i.kategori,
           v.harga_jual_besar AS harga_jual,
           v.harga_jual_kecil AS harga_eceran,
           v.berat AS berat_gram,
           i.gambar,
           v.sku_variasi,
           i.deskripsi,
           COALESCE(sc.stok, 0) AS stok_sistem,
           v.tampil_di_online, v.satuan_besar, v.satuan_kecil, v.rasio_konversi
    FROM produk_variasi v
    JOIN produk_induk i ON v.id_produk_induk = i.id
    LEFT JOIN stok_toko sc ON sc.id_variasi = v.id 
    WHERE v.is_active = 1 AND i.is_active = 1 AND v.tampil_di_online = 1
    ORDER BY i.kategori ASC, i.nama_produk ASC, v.id ASC
");
$stmtProduk->execute();
$products = $stmtProduk->fetchAll();

// Category counts
$catCounts = [];
foreach ($products as $p) {
    $catCounts[$p['kategori']] = ($catCounts[$p['kategori']] ?? 0) + 1;
}
ksort($catCounts);

// Hero slide products (with images first)
$slideProds = array_values(array_filter($products, fn($p) => !empty($p['gambar'])));
if (empty($slideProds)) $slideProds = array_values($products);
$slideProds = array_slice($slideProds, 0, 5);

// Featured products (first 8)
$featuredProds = array_slice($products, 0, 8);
?>

<style>
/* ── Hero Slideshow ── */
.hero-slide { display:none; opacity:0; transition:opacity .6s ease; }
.hero-slide.active { display:flex; opacity:1; }
.slide-dot { width:8px; height:8px; border-radius:50%; background:rgba(255,255,255,.35); cursor:pointer; transition:all .25s; }
.slide-dot.active { background:#fff; width:28px; border-radius:4px; }

/* ── Product Card ── */
.prod-card {
    transition: transform .22s cubic-bezier(.4,0,.2,1), box-shadow .22s;
    border: 1.5px solid #e4e9f0;
}
.prod-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 48px rgba(26,117,210,.15);
    border-color: #1a75d2;
}
.prod-card:hover .card-img img { transform: scale(1.06); }
.card-img img { transition: transform .3s ease; }

/* ── Category Pill Card ── */
.cat-pill { transition: all .2s; }
.cat-pill:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(26,117,210,.12); }

/* ── Trust Badges Scroll animation ── */
@keyframes slideInLeft { from{opacity:0;transform:translateX(-20px)} to{opacity:1;transform:none} }
.trust-badge { animation: slideInLeft .4s ease backwards; }
</style>

<main class="flex-1 w-full">

    <!-- ============================================================ -->
    <!-- HERO BANNER SLIDESHOW                                         -->
    <!-- ============================================================ -->
    <section class="relative overflow-hidden bg-gradient-to-br from-[#0f2d5a] via-[#0d3670] to-[#1a75d2]" style="min-height:380px;">

        <!-- Background pattern (dot grid) -->
        <div class="absolute inset-0 opacity-[.06]" style="background-image:radial-gradient(circle,white 1px,transparent 1px);background-size:24px 24px;"></div>

        <?php foreach ($slideProds as $si => $sp):
            $rasioH = intval($sp['rasio_konversi']) ?: 1;
            $stokBoxH = floor(intval($sp['stok_sistem']) / $rasioH);
        ?>
        <div class="hero-slide absolute inset-0 flex flex-col md:flex-row items-center justify-between gap-0 px-6 sm:px-12 lg:px-20 py-12 <?= $si === 0 ? 'active' : '' ?>" data-slide="<?= $si ?>">
            <!-- Text -->
            <div class="flex-1 text-white z-10 max-w-lg mb-6 md:mb-0">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-white/20 bg-white/10 text-xs font-bold uppercase tracking-widest text-blue-100 mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <?= htmlspecialchars($sp['kategori']) ?>
                </span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight tracking-tight mb-4">
                    <?= htmlspecialchars($sp['nama_induk']) ?>
                </h1>
                <p class="text-blue-100/85 text-sm sm:text-base leading-relaxed mb-6 line-clamp-3">
                    <?= htmlspecialchars(mb_strimwidth($sp['deskripsi'] ?? 'Produk medis berkualitas tinggi dengan jaminan mutu dan izin resmi Kemenkes RI.', 0, 130, '…')) ?>
                </p>
                <div class="flex items-center gap-3 flex-wrap">
                    <a href="produk.php?id=<?= $sp['id'] ?>" class="px-6 py-3 bg-white text-[#0f2d5a] font-extrabold text-sm rounded-xl hover:bg-blue-50 transition shadow-lg active:scale-95">
                        Lihat Detail &rarr;
                    </a>
                    <a href="#katalog" class="px-6 py-3 bg-white/10 border border-white/25 text-white font-bold text-sm rounded-xl hover:bg-white/20 transition backdrop-blur-sm">
                        Semua Produk
                    </a>
                    <?php if ($stokBoxH > 0): ?>
                    <span class="text-xs font-semibold text-emerald-300 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Tersedia: <?= $stokBoxH ?> <?= htmlspecialchars($sp['satuan_besar'] ?: 'Unit') ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Image -->
            <div class="shrink-0 flex items-center justify-center md:w-64 lg:w-80 relative">
                <!-- glow -->
                <div class="absolute inset-0 bg-white/5 rounded-full blur-3xl scale-125"></div>
                <?php if (!empty($sp['gambar'])): ?>
                    <img src="../<?= htmlspecialchars($sp['gambar']) ?>" alt="<?= htmlspecialchars($sp['nama_induk']) ?>"
                         class="relative h-52 sm:h-64 lg:h-72 object-contain drop-shadow-2xl hover:scale-105 transition-transform duration-500"
                         onerror="this.style.opacity='.3'">
                <?php else: ?>
                    <div class="w-48 h-48 rounded-3xl bg-white/10 border border-white/20 flex items-center justify-center">
                        <svg class="w-24 h-24 text-white/25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12h6M12 9v6"/></svg>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Slide Dots -->
        <?php if (count($slideProds) > 1): ?>
        <div class="absolute bottom-6 left-0 right-0 flex items-center justify-center gap-2 z-20">
            <?php foreach ($slideProds as $si => $sp): ?>
            <span class="slide-dot <?= $si === 0 ? 'active' : '' ?>" onclick="goSlide(<?= $si ?>)"></span>
            <?php endforeach; ?>
        </div>
        <button onclick="prevSlide()" class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 bg-white/10 hover:bg-white/25 border border-white/20 rounded-full flex items-center justify-center text-white transition backdrop-blur-sm">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <button onclick="nextSlide()" class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 bg-white/10 hover:bg-white/25 border border-white/20 rounded-full flex items-center justify-center text-white transition backdrop-blur-sm">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        <?php endif; ?>
    </section>

    <!-- ============================================================ -->
    <!-- TRUST BADGES                                                  -->
    <!-- ============================================================ -->
    <div class="bg-white border-b border-slate-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 py-5">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php
                $badges = [
                    ['icon'=>'<path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>', 'label'=>'Produk Bergaransi Resmi', 'sub'=>'Izin Edar Kemenkes RI', 'color'=>'bg-blue-50 text-[#1a75d2]'],
                    ['icon'=>'<path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>', 'label'=>'Pengiriman Nasional', 'sub'=>'Ekspedisi Seluruh Indonesia', 'color'=>'bg-emerald-50 text-emerald-700'],
                    ['icon'=>'<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>', 'label'=>'100% Produk Original', 'sub'=>'Tersegel & Bersertifikat', 'color'=>'bg-violet-50 text-violet-700'],
                    ['icon'=>'<path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>', 'label'=>'Pembayaran Aman', 'sub'=>'Midtrans & Transfer Bank', 'color'=>'bg-amber-50 text-amber-700'],
                ];
                foreach ($badges as $bi => $b): ?>
                <div class="trust-badge flex items-center gap-3" style="animation-delay:<?= $bi * 80 ?>ms">
                    <div class="w-11 h-11 rounded-2xl <?= $b['color'] ?> flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><?= $b['icon'] ?></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-[#1e293b] leading-none mb-0.5"><?= $b['label'] ?></p>
                        <p class="text-[11px] text-[#64748b]"><?= $b['sub'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-10">

        <!-- ============================================================ -->
        <!-- PROMO BANNERS (Pharmify-style 3-col grid)                    -->
        <!-- ============================================================ -->
        <section class="mb-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-[#0f2d5a] to-[#1a75d2] rounded-2xl p-6 text-white relative overflow-hidden group hover:-translate-y-1 transition-transform duration-200">
                    <div class="absolute -right-6 -top-6 w-28 h-28 bg-white/5 rounded-full"></div>
                    <div class="absolute -right-2 -bottom-4 w-20 h-20 bg-white/5 rounded-full"></div>
                    <p class="text-xs font-extrabold uppercase tracking-widest text-blue-200 mb-2">Unggulan</p>
                    <h3 class="text-lg font-extrabold mb-1 leading-tight">Alat Kesehatan<br>Bergaransi Resmi</h3>
                    <p class="text-xs text-blue-200 mb-4">Tensimeter, Kursi Roda, dll.</p>
                    <a href="kategori.php?kategori=Alat+Kesehatan" class="inline-flex items-center gap-1.5 text-xs font-bold bg-white text-[#1a75d2] px-4 py-2 rounded-xl hover:bg-blue-50 transition shadow-sm">
                        Lihat Produk
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
                    </a>
                </div>
                <div class="bg-gradient-to-br from-emerald-700 to-emerald-600 rounded-2xl p-6 text-white relative overflow-hidden group hover:-translate-y-1 transition-transform duration-200">
                    <div class="absolute -right-6 -top-6 w-28 h-28 bg-white/5 rounded-full"></div>
                    <div class="absolute -right-2 -bottom-4 w-20 h-20 bg-white/5 rounded-full"></div>
                    <p class="text-xs font-extrabold uppercase tracking-widest text-emerald-200 mb-2">Stok FEFO</p>
                    <h3 class="text-lg font-extrabold mb-1 leading-tight">Obat-Obatan<br>Kadaluarsa Terpantau</h3>
                    <p class="text-xs text-emerald-200 mb-4">Sistem FEFO otomatis terjamin.</p>
                    <a href="kategori.php?kategori=Obat" class="inline-flex items-center gap-1.5 text-xs font-bold bg-white text-emerald-700 px-4 py-2 rounded-xl hover:bg-emerald-50 transition shadow-sm">
                        Lihat Produk
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
                    </a>
                </div>
                <div class="bg-gradient-to-br from-slate-800 to-slate-700 rounded-2xl p-6 text-white relative overflow-hidden group hover:-translate-y-1 transition-transform duration-200">
                    <div class="absolute -right-6 -top-6 w-28 h-28 bg-white/5 rounded-full"></div>
                    <p class="text-xs font-extrabold uppercase tracking-widest text-slate-300 mb-2">Multi-Cabang</p>
                    <h3 class="text-lg font-extrabold mb-1 leading-tight">Stok Real-time<br>Seluruh Cabang</h3>
                    <p class="text-xs text-slate-400 mb-4">Sinkronisasi omnichannel terpadu.</p>
                    <a href="kategori.php" class="inline-flex items-center gap-1.5 text-xs font-bold bg-white text-slate-800 px-4 py-2 rounded-xl hover:bg-slate-50 transition shadow-sm">
                        Lihat Semua
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- CATEGORY CHIPS                                                -->
        <!-- ============================================================ -->
        <section class="mb-12">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-xl font-extrabold text-[#1e293b] tracking-tight">Belanja per Kategori</h2>
                    <p class="text-sm text-[#64748b] mt-0.5">Temukan produk sesuai kebutuhan medis Anda</p>
                </div>
                <a href="kategori.php" class="text-sm font-bold text-[#1a75d2] hover:text-[#1562b3] flex items-center gap-1 transition">
                    Semua Kategori
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
                </a>
            </div>

            <?php
            $catIcons = [
                'Obat' => '<path d="m10.5 20.5 10-10a4.95 4.95 0 10-7-7l-10 10a4.95 4.95 0 107 7z"/><line x1="8.5" y1="8.5" x2="15.5" y2="15.5"/>',
                'Alat Kesehatan' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12h6M12 9v6"/>',
                'Vitamin & Suplemen' => '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>',
                'Skincare Medis' => '<circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01"/>',
            ];
            $catBg = ['Obat'=>'bg-blue-50 text-[#1a75d2] border-blue-100', 'Alat Kesehatan'=>'bg-emerald-50 text-emerald-700 border-emerald-100', 'default'=>'bg-slate-50 text-slate-700 border-slate-100'];
            ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-<?= min(6, max(2, count($catCounts))) ?> gap-3">
                <?php foreach ($catCounts as $catName => $count):
                    $iconPath = $catIcons[$catName] ?? '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>';
                    $bgCls = $catBg[$catName] ?? $catBg['default'];
                ?>
                <a href="kategori.php?kategori=<?= urlencode($catName) ?>"
                   class="cat-pill bg-white border border-slate-200 rounded-2xl p-4 flex flex-col items-center text-center group hover:border-[#1a75d2] hover:shadow-md">
                    <div class="w-12 h-12 rounded-2xl <?= $bgCls ?> border flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-200">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><?= $iconPath ?></svg>
                    </div>
                    <span class="text-sm font-bold text-[#1e293b] group-hover:text-[#1a75d2] transition-colors line-clamp-2 leading-tight mb-1"><?= htmlspecialchars($catName) ?></span>
                    <span class="text-xs text-[#64748b]"><?= $count ?> Produk</span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- PRODUCT CATALOG                                               -->
        <!-- ============================================================ -->
        <section id="katalog">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-5 border-b border-slate-200">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h2 class="text-xl font-extrabold text-[#1e293b] tracking-tight">Semua Produk</h2>
                        <span class="px-3 py-0.5 bg-[#e8f2ff] text-[#1a75d2] font-extrabold text-xs rounded-full border border-[#1a75d2]/20"><?= count($products) ?> Produk</span>
                    </div>
                    <p class="text-sm text-[#64748b]">Klik produk untuk detail spesifikasi dan pembelian.</p>
                </div>
                <select id="sort_select" onchange="sortProducts()" class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white focus:outline-none focus:border-[#1a75d2] text-[#1e293b] font-medium shadow-xs w-full sm:w-auto">
                    <option value="default">Urutan Standar</option>
                    <option value="price_asc">Harga Terendah</option>
                    <option value="price_desc">Harga Tertinggi</option>
                    <option value="name_asc">Nama (A–Z)</option>
                    <option value="stok_desc">Stok Terbanyak</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="product-grid">
                <?php foreach ($products as $p):
                    $stokPcs  = intval($p['stok_sistem']);
                    $rasio    = intval($p['rasio_konversi']) ?: 1;
                    $stokBox  = floor($stokPcs / $rasio);
                    $harga    = floatval($p['harga_jual']);
                    $hargaEcr = floatval($p['harga_eceran']);
                    $satB     = htmlspecialchars($p['satuan_besar'] ?: 'Box');
                    $satK     = htmlspecialchars($p['satuan_kecil'] ?: 'Pcs');
                    $desc     = trim($p['deskripsi'] ?? '');
                    $descSh   = $desc ? mb_strimwidth($desc, 0, 75, '…') : 'Produk medis berkualitas dengan jaminan mutu resmi.';

                    if ($stokBox <= 0)     { $stokCls='text-rose-600'; $dotCls='bg-rose-500'; $stokLbl='Habis'; }
                    elseif ($stokBox <= 5) { $stokCls='text-amber-700'; $dotCls='bg-amber-400'; $stokLbl="Sisa $stokBox $satB"; }
                    else                   { $stokCls='text-emerald-700'; $dotCls='bg-emerald-500'; $stokLbl="$stokBox $satB"; }
                ?>
                <div class="prod-card group bg-white rounded-2xl overflow-hidden flex flex-col"
                     data-name="<?= strtolower(htmlspecialchars($p['nama_produk'])) ?>"
                     data-price="<?= $harga ?>"
                     data-stok="<?= $stokBox ?>"
                     data-id="<?= $p['id'] ?>">

                    <!-- Image -->
                    <a href="produk.php?id=<?= $p['id'] ?>" class="card-img relative block bg-gradient-to-br from-slate-50 to-blue-50/30 overflow-hidden" style="height:210px;">
                        <?php if (!empty($p['gambar'])): ?>
                            <img src="../<?= htmlspecialchars($p['gambar']) ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>"
                                 class="w-full h-full object-contain p-5"
                                 onerror="this.onerror=null; this.classList.add('opacity-30');">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-20 h-20 text-slate-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12h6M12 9v6"/></svg>
                            </div>
                        <?php endif; ?>

                        <!-- Category badge -->
                        <span class="absolute top-3 left-3 px-2.5 py-1 bg-white/95 text-[#1a75d2] text-[10px] font-bold rounded-lg border border-[#1a75d2]/15 shadow-xs">
                            <?= htmlspecialchars($p['kategori']) ?>
                        </span>
                        <!-- Stock badge -->
                        <span class="absolute top-3 right-3 flex items-center gap-1.5 px-2.5 py-1 bg-white/95 text-[10px] font-bold rounded-lg shadow-xs <?= $stokCls ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?= $dotCls ?>"></span>
                            <?= $stokLbl ?>
                        </span>
                    </a>

                    <!-- Card Body -->
                    <div class="p-4 flex flex-col flex-1">
                        <a href="produk.php?id=<?= $p['id'] ?>" class="block mb-3">
                            <h3 class="text-sm font-extrabold text-[#1e293b] leading-snug line-clamp-2 group-hover:text-[#1a75d2] transition-colors">
                                <?= htmlspecialchars($p['nama_induk']) ?>
                            </h3>
                            <span class="text-xs text-[#64748b]"><?= htmlspecialchars($p['nama_variasi']) ?></span>
                        </a>

                        <!-- Specs Mini-table -->
                        <div class="rounded-xl bg-slate-50 border border-slate-100 divide-y divide-slate-100 mb-4 text-xs">
                            <div class="flex gap-2 px-3 py-2">
                                <span class="text-[#64748b] w-[72px] shrink-0 font-medium">Keterangan</span>
                                <span class="font-medium text-[#64748b] line-clamp-2"><?= htmlspecialchars($descSh) ?></span>
                            </div>
                            <div class="flex gap-2 px-3 py-2">
                                <span class="text-[#64748b] w-[72px] shrink-0 font-medium">Kemasan</span>
                                <span class="font-bold text-[#1e293b]">1 <?= $satB ?> = <?= $rasio ?> <?= $satK ?></span>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="mb-4 mt-auto">
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-lg font-extrabold text-[#1e293b]">Rp <?= number_format($harga, 0, ',', '.') ?></span>
                                <span class="text-xs text-[#64748b] font-medium">/ <?= $satB ?></span>
                            </div>
                            <?php if ($hargaEcr > 0 && $hargaEcr != $harga / $rasio): ?>
                            <div class="text-[10px] text-slate-400 mt-0.5">~Rp <?= number_format($hargaEcr, 0, ',', '.') ?> / <?= $satK ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- CTA Buttons -->
                        <?php if ($stokBox >= 1): ?>
                        <div class="flex items-center gap-2 mb-2.5">
                            <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl overflow-hidden flex-1">
                                <button type="button" onclick="adjustQty(<?= $p['id'] ?>, -1, <?= $stokBox ?>)"
                                    class="w-9 h-9 flex items-center justify-center hover:bg-slate-100 text-[#1e293b] font-black border-r border-slate-200 transition text-base">−</button>
                                <input type="number" id="qty_<?= $p['id'] ?>" value="1" min="1" max="<?= $stokBox ?>" readonly
                                    class="flex-1 text-center text-sm font-bold py-2 bg-transparent focus:outline-none select-none">
                                <button type="button" onclick="adjustQty(<?= $p['id'] ?>, 1, <?= $stokBox ?>)"
                                    class="w-9 h-9 flex items-center justify-center hover:bg-slate-100 text-[#1e293b] font-black border-l border-slate-200 transition text-base">+</button>
                            </div>
                            <span class="text-[10px] text-slate-400 shrink-0">Max <?= $stokBox ?></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <button onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars_decode($p['nama_produk'])) ?>', <?= $harga ?>, <?= intval($p['berat_gram']) ?>, '<?= addslashes($p['gambar'] ?? '') ?>', '<?= addslashes($satB) ?>', <?= $rasio ?>, <?= $stokBox ?>, document.getElementById('qty_<?= $p['id'] ?>').value)"
                                class="flex items-center justify-center gap-1.5 text-xs font-bold py-2.5 rounded-xl bg-[#e8f2ff] hover:bg-blue-100 text-[#1a75d2] border border-[#1a75d2]/20 cursor-pointer active:scale-95 transition">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.55L23 6H6"/></svg>
                                Keranjang
                            </button>
                            <button onclick="buyNow(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars_decode($p['nama_produk'])) ?>', <?= $harga ?>, <?= intval($p['berat_gram']) ?>, '<?= addslashes($p['gambar'] ?? '') ?>', '<?= addslashes($satB) ?>', <?= $rasio ?>, <?= $stokBox ?>, document.getElementById('qty_<?= $p['id'] ?>').value)"
                                class="flex items-center justify-center gap-1.5 text-xs font-bold py-2.5 rounded-xl bg-[#1a75d2] hover:bg-[#1562b3] text-white cursor-pointer active:scale-95 transition shadow-sm">
                                Beli Sekarang
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="w-full py-3 px-4 rounded-xl bg-slate-100 text-slate-400 text-center text-xs font-semibold border border-slate-200">
                            Stok Habis
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($products)): ?>
                <div class="col-span-full py-20 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 mx-auto mb-4 flex items-center justify-center">
                        <svg class="w-8 h-8 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    </div>
                    <p class="text-base font-semibold text-[#64748b] mb-1">Belum ada produk tersedia</p>
                    <p class="text-sm text-slate-400">Coba ganti cabang atau hubungi kami.</p>
                </div>
                <?php endif; ?>
            </div>
        </section>

    </div><!-- /max-w-7xl -->
</main>

<script>
// ── Slideshow ──────────────────────────────────────────────────────
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
function restartTimer() {
    clearInterval(slideTimer);
    if(slides.length > 1) slideTimer = setInterval(() => goSlide(curSlide + 1), 4500);
}
if (slides.length > 1) restartTimer();

// ── Sorting ────────────────────────────────────────────────────────
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
    cards.forEach(c => grid.appendChild(c));
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
