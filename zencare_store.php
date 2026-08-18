<?php
// File: zencare_store.php
// ZenCare Medical E-Commerce Store (Premium Design v4)
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/koneksi.php';

if (isset($_GET['set_cabang'])) {
    $_SESSION['id_cabang'] = intval($_GET['set_cabang']);
}
if (!isset($_SESSION['id_cabang']) || $_SESSION['id_cabang'] <= 0) {
    $_SESSION['id_cabang'] = 1;
}
$activeCabangId = $_SESSION['id_cabang'];

$stmtCabangAktif = $pdo->prepare("SELECT * FROM cabang WHERE id = ? AND is_active = 1");
$stmtCabangAktif->execute([$activeCabangId]);
$cabangAktif = $stmtCabangAktif->fetch();

$daftarCabang = $pdo->query("SELECT * FROM cabang WHERE is_active = 1 ORDER BY id ASC")->fetchAll();

$stmtWeb = $pdo->query("SELECT * FROM pengaturan_web WHERE id=1");
$webCfg  = $stmtWeb->fetch() ?: [];
$namaToko = $webCfg['nama_toko'] ?? 'ZenCare Medical';
$logoUrl  = $webCfg['logo_url']  ?? '';

$stmtProduk = $pdo->prepare("
    SELECT v.id, CONCAT(i.nama_produk, ' - ', v.nama_variasi) AS nama_produk,
           i.kategori, v.harga_jual_besar AS harga_jual, v.berat AS berat_gram, v.gambar,
           i.deskripsi, COALESCE(sc.stok, 0) AS stok_sistem,
           v.tampil_di_online, v.satuan_besar, v.satuan_kecil, v.rasio_konversi
    FROM produk_variasi v
    JOIN produk_induk i ON v.id_produk_induk = i.id
    LEFT JOIN stok_cabang sc ON sc.id_variasi = v.id AND sc.id_cabang = ?
    WHERE v.is_active = 1 AND i.is_active = 1 AND v.tampil_di_online = 1
    ORDER BY i.kategori ASC, v.id ASC
");
$stmtProduk->execute([$activeCabangId]);
$products = $stmtProduk->fetchAll();

// Get unique categories
$categories = array_unique(array_column($products, 'kategori'));
sort($categories);

// Build stok-aware product JSON for JS
$productMeta = [];
foreach ($products as $p) {
    $rasio = intval($p['rasio_konversi']) ?: 1;
    $stokBox = floor(intval($p['stok_sistem']) / $rasio);
    $productMeta[$p['id']] = ['stokBox' => $stokBox, 'satBesar' => $p['satuan_besar']];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($namaToko) ?> - Distributor Resmi Alkes &amp; Medis</title>
    <meta name="description" content="Toko online alat kesehatan dan obat-obatan terpercaya. Pembelian grosir dengan pengiriman cepat ke seluruh Indonesia.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
      tailwind.config = {
        theme: { extend: {
          fontFamily: { sans: ['Inter', 'sans-serif'] },
          colors: {
            zc:    '#1a75d2', zcHv:  '#1562b3', zcLt:  '#e8f2ff',
            zcEm:  '#059669', zcBrd: '#e4e9f0', zcTxt: '#1e293b', zcMut: '#64748b',
          }
        }}
      }
    </script>
    <style>
        * { box-sizing: border-box; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        
        /* Hero Slider */
        .slide { display: none; animation: fadeSlide 0.5s ease; }
        .slide.active { display: flex; }
        @keyframes fadeSlide { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
        
        /* Product Card */
        .product-card { transition: transform .2s ease, box-shadow .2s ease; }
        .product-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(26,117,210,.12); }
        
        /* Category Tab */
        .cat-tab.active { background: #1a75d2; color: white; border-color: #1a75d2; }
        .cat-tab { transition: all .15s; }
        
        /* Cart Badge animate */
        @keyframes pop { 0%,100%{transform:scale(1)} 50%{transform:scale(1.35)} }
        .pop { animation: pop .25s ease; }
    </style>
</head>
<body class="bg-[#f0f4f9] text-zcTxt font-sans antialiased">

    <!-- =========================================================== -->
    <!-- HEADER NAVBAR                                                 -->
    <!-- =========================================================== -->
    <header class="bg-white border-b border-zcBrd sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 h-16 flex items-center justify-between gap-4">
            <!-- Brand -->
            <div class="flex items-center gap-3 shrink-0">
                <?php if ($logoUrl): ?>
                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" class="h-8 w-auto">
                <?php else: ?>
                    <div class="w-9 h-9 rounded-xl bg-zc flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                    </div>
                <?php endif; ?>
                <div class="hidden sm:block">
                    <a href="zencare_store.php" class="text-[15px] font-bold text-zcTxt leading-tight block"><?= htmlspecialchars($namaToko) ?></a>
                    <span class="text-[10px] text-zcMut font-medium uppercase tracking-wide">Distributor Resmi Alkes &amp; Medis</span>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="flex-1 max-w-md hidden md:block">
                <div class="relative">
                    <svg class="w-4 h-4 text-zcMut absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input id="store_search" type="text" onkeyup="filterProducts()" placeholder="Cari produk, alkes, obat..."
                        class="w-full text-xs pl-10 pr-4 py-2.5 border border-zcBrd rounded-xl bg-slate-50 focus:outline-none focus:border-zc focus:bg-white transition">
                </div>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-2 shrink-0">
                <!-- Branch Selector -->
                <form method="GET" class="hidden sm:flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-zcMut" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <select name="set_cabang" onchange="this.form.submit()" class="bg-white text-zcTxt text-xs border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc">
                        <?php foreach ($daftarCabang as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $activeCabangId == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <?php if (isset($_SESSION['user_id']) && in_array($_SESSION['role'] ?? '', ['super_admin','kasir'])): ?>
                    <a href="index.php" class="text-xs font-medium text-zcMut hover:text-zcTxt border border-zcBrd px-3 py-1.5 rounded-lg bg-white transition hidden sm:block">Dashboard</a>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center gap-1.5 border border-zcBrd px-2.5 py-1.5 rounded-lg bg-slate-50 text-xs">
                        <div class="w-5 h-5 rounded-full bg-zc text-white font-bold text-[10px] flex items-center justify-center">
                            <?= strtoupper(substr($_SESSION['nama_lengkap'] ?? 'U', 0, 1)) ?>
                        </div>
                        <span class="font-semibold text-zcTxt hidden sm:inline"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? '') ?></span>
                        <a href="logout.php" class="text-rose-500 hover:text-rose-700 ml-1">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="login_customer.php" class="text-xs font-medium border border-zcBrd px-3 py-1.5 rounded-lg bg-white text-zcMut hover:text-zcTxt transition">Masuk</a>
                    <a href="register.php" class="text-xs font-semibold bg-zcLt text-zc border border-zc/20 px-3 py-1.5 rounded-lg hover:bg-zc/10 transition">Daftar</a>
                <?php endif; ?>

                <!-- Cart Button -->
                <a href="zencare_checkout.php" class="relative inline-flex items-center gap-2 px-4 py-2 bg-zc hover:bg-zcHv text-white text-xs font-bold rounded-xl transition shadow-sm">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.55L23 6H6"/></svg>
                    <span class="hidden sm:inline">Keranjang</span>
                    <span id="cart-badge" class="px-1.5 py-0.5 text-[10px] font-bold bg-white text-zc rounded-full min-w-[18px] text-center">0</span>
                </a>
            </div>
        </div>
    </header>

    <!-- =========================================================== -->
    <!-- HERO BANNER SLIDER                                           -->
    <!-- =========================================================== -->
    <section class="max-w-7xl mx-auto px-4 lg:px-8 pt-6 pb-4">
        <div class="relative rounded-2xl overflow-hidden shadow-lg" id="hero-slider">
            
            <!-- Slide 1 -->
            <div class="slide active min-h-[220px] sm:min-h-[260px] bg-gradient-to-r from-[#0f2d5a] via-[#1a4a8a] to-[#1a75d2] text-white items-center gap-8 p-8 sm:p-12">
                <div class="flex-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/15 border border-white/25 rounded-full text-xs font-bold uppercase tracking-wider mb-4">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        Distributor Resmi
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight leading-tight mb-3">Alat Kesehatan &amp;<br>Medis Bergaransi Resmi</h1>
                    <p class="text-blue-100 text-sm leading-relaxed mb-5 max-w-md">Pembelian grosir langsung ke distributor. Kualitas terjamin, harga kompetitif, pengiriman ke seluruh Indonesia.</p>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-white/10 border border-white/20 rounded-lg">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            100% Original
                        </span>
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-white/10 border border-white/20 rounded-lg">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            Garansi Resmi
                        </span>
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-white/10 border border-white/20 rounded-lg">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M14 18V6a2 2 0 00-2-2H4a2 2 0 00-2 2v11a1 1 0 001 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 001-1v-3.65a1 1 0 00-.22-.624l-3.48-4.35A1 1 0 0017.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
                            Kirim Cepat Malang
                        </span>
                    </div>
                </div>
                <div class="hidden lg:flex items-center justify-center w-48 h-36 bg-white/10 rounded-2xl border border-white/20">
                    <svg class="w-20 h-20 text-white/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="slide min-h-[220px] sm:min-h-[260px] bg-gradient-to-r from-[#064e3b] via-[#065f46] to-[#059669] text-white items-center gap-8 p-8 sm:p-12">
                <div class="flex-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/15 border border-white/25 rounded-full text-xs font-bold uppercase tracking-wider mb-4">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                        Pembelian Grosir B2B
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight leading-tight mb-3">Harga Grosir Khusus<br>Klinik &amp; Apotek</h2>
                    <p class="text-emerald-100 text-sm leading-relaxed mb-5 max-w-md">Sistem B2B modern untuk klinik, rumah sakit, dan apotek. Kelola pesanan grosir langsung dari dashboard Anda.</p>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-white/10 border border-white/20 rounded-lg">Harga Khusus Grosir</span>
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-white/10 border border-white/20 rounded-lg">Invoice Otomatis</span>
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-white/10 border border-white/20 rounded-lg">Multi Ekspedisi</span>
                    </div>
                </div>
                <div class="hidden lg:flex items-center justify-center w-48 h-36 bg-white/10 rounded-2xl border border-white/20">
                    <svg class="w-20 h-20 text-white/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="slide min-h-[220px] sm:min-h-[260px] bg-gradient-to-r from-[#4c1d95] via-[#5b21b6] to-[#7c3aed] text-white items-center gap-8 p-8 sm:p-12">
                <div class="flex-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/15 border border-white/25 rounded-full text-xs font-bold uppercase tracking-wider mb-4">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
                        Kualitas Terjamin
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight leading-tight mb-3">Produk Medis Tersertifikasi<br>BPOM &amp; Kemenkes RI</h2>
                    <p class="text-purple-100 text-sm leading-relaxed mb-5 max-w-md">Semua produk kami melewati seleksi ketat dan bersertifikat resmi dari lembaga berwenang Republik Indonesia.</p>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-white/10 border border-white/20 rounded-lg">Sertifikat BPOM</span>
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-white/10 border border-white/20 rounded-lg">ISO 13485</span>
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-white/10 border border-white/20 rounded-lg">CE Marking</span>
                    </div>
                </div>
                <div class="hidden lg:flex items-center justify-center w-48 h-36 bg-white/10 rounded-2xl border border-white/20">
                    <svg class="w-20 h-20 text-white/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
            </div>

            <!-- Slider Controls -->
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                <button onclick="goToSlide(0)" class="slide-dot w-2 h-2 rounded-full bg-white/50 hover:bg-white transition" data-slide="0"></button>
                <button onclick="goToSlide(1)" class="slide-dot w-2 h-2 rounded-full bg-white/50 hover:bg-white transition" data-slide="1"></button>
                <button onclick="goToSlide(2)" class="slide-dot w-2 h-2 rounded-full bg-white/50 hover:bg-white transition" data-slide="2"></button>
            </div>
            <button onclick="prevSlide()" class="absolute left-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-white/20 hover:bg-white/40 rounded-full flex items-center justify-center text-white transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <button onclick="nextSlide()" class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-white/20 hover:bg-white/40 rounded-full flex items-center justify-center text-white transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
            </button>
        </div>
    </section>

    <!-- =========================================================== -->
    <!-- INFO BAR                                                      -->
    <!-- =========================================================== -->
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-3">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <?php
            $infos = [
                ['icon' => '<path d="M14 18V6a2 2 0 00-2-2H4a2 2 0 00-2 2v11a1 1 0 001 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 001-1v-3.65a1 1 0 00-.22-.624l-3.48-4.35A1 1 0 0017.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>', 'title' => 'Pengiriman Cepat', 'sub' => 'Malang & Nasional', 'color' => 'text-blue-600 bg-blue-50'],
                ['icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/>', 'title' => 'Produk Original', 'sub' => 'Bersertifikat resmi', 'color' => 'text-emerald-600 bg-emerald-50'],
                ['icon' => '<rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/>', 'title' => 'Bayar Aman', 'sub' => 'Midtrans Sandbox', 'color' => 'text-purple-600 bg-purple-50'],
                ['icon' => '<path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.15 1.18 2 2 0 012.11 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.09a16 16 0 006 6l.45-.45a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 14.92z"/>', 'title' => 'Layanan 24/7', 'sub' => 'Hubungi kami kapan saja', 'color' => 'text-amber-600 bg-amber-50'],
            ];
            foreach ($infos as $info): ?>
            <div class="bg-white border border-zcBrd rounded-xl p-3 flex items-center gap-3 shadow-xs">
                <div class="w-9 h-9 rounded-lg <?= explode(' ', $info['color'])[1] ?> flex items-center justify-center shrink-0">
                    <svg class="w-4.5 h-4.5 <?= explode(' ', $info['color'])[0] ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <?= $info['icon'] ?>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-zcTxt leading-tight"><?= $info['title'] ?></p>
                    <p class="text-[10px] text-zcMut"><?= $info['sub'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- =========================================================== -->
    <!-- MAIN CATALOG                                                  -->
    <!-- =========================================================== -->
    <main class="max-w-7xl mx-auto px-4 lg:px-8 py-6">

        <!-- Section Title & Filters -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-lg font-bold text-zcTxt">Katalog Produk</h2>
                <p class="text-xs text-zcMut mt-0.5"><?= count($products) ?> produk tersedia &bull; Cabang: <strong class="text-zcTxt"><?= htmlspecialchars($cabangAktif['nama'] ?? '') ?></strong></p>
            </div>
            <!-- Sort -->
            <select id="sort_select" onchange="sortProducts()" class="text-xs border border-zcBrd rounded-xl px-3 py-2 bg-white focus:outline-none focus:border-zc sm:w-48">
                <option value="default">Urutan Default</option>
                <option value="price_asc">Harga Terendah</option>
                <option value="price_desc">Harga Tertinggi</option>
                <option value="name_asc">Nama A–Z</option>
            </select>
        </div>

        <!-- Category Tabs -->
        <div class="flex gap-2 overflow-x-auto pb-3 mb-5 scrollbar-thin" id="cat-tabs">
            <button onclick="filterCat('semua')" class="cat-tab active shrink-0 px-4 py-2 text-xs font-semibold border border-zcBrd rounded-full bg-white text-zcMut hover:border-zc hover:text-zc" data-cat="semua">
                Semua Produk
            </button>
            <?php foreach ($categories as $cat): ?>
            <button onclick="filterCat('<?= htmlspecialchars(addslashes($cat)) ?>')" class="cat-tab shrink-0 px-4 py-2 text-xs font-semibold border border-zcBrd rounded-full bg-white text-zcMut hover:border-zc hover:text-zc" data-cat="<?= htmlspecialchars($cat) ?>">
                <?= htmlspecialchars($cat) ?>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Product Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5" id="product-grid">
            <?php foreach ($products as $p):
                $stokPcs = intval($p['stok_sistem']);
                $rasio   = intval($p['rasio_konversi']) ?: 1;
                $stokBox = floor($stokPcs / $rasio);
                $harga   = $p['harga_jual'];
                $satBesar = $p['satuan_besar'];
                $satKecil = $p['satuan_kecil'];
            ?>
            <div class="product-card bg-white border border-zcBrd rounded-2xl overflow-hidden flex flex-col shadow-sm"
                 data-cat="<?= htmlspecialchars($p['kategori']) ?>"
                 data-name="<?= strtolower(htmlspecialchars($p['nama_produk'])) ?>"
                 data-price="<?= $harga ?>"
                 data-id="<?= $p['id'] ?>">

                <!-- Product Image -->
                <div class="relative h-44 bg-gradient-to-br from-slate-50 to-slate-100 overflow-hidden border-b border-zcBrd">
                    <?php if ($p['gambar']): ?>
                        <img src="<?= htmlspecialchars($p['gambar']) ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex flex-col items-center justify-center gap-2 text-slate-300">
                            <svg class="w-14 h-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                            <span class="text-[10px] font-medium">No Image</span>
                        </div>
                    <?php endif; ?>

                    <!-- Stock Badge -->
                    <div class="absolute top-2.5 right-2.5">
                        <?php if ($stokBox >= 1): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-500 text-white text-[10px] font-bold rounded-full shadow-sm">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6L9 17l-5-5"/></svg>
                                Stok: <?= $stokBox ?> <?= htmlspecialchars($satBesar) ?>
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-500 text-white text-[10px] font-bold rounded-full shadow-sm">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                Habis
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Category Tag -->
                    <div class="absolute top-2.5 left-2.5">
                        <span class="px-2 py-0.5 bg-white/90 backdrop-blur-sm text-zc text-[10px] font-bold rounded-lg border border-zc/20">
                            <?= htmlspecialchars($p['kategori']) ?>
                        </span>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="p-4 flex flex-col flex-1">
                    <h3 class="text-sm font-bold text-zcTxt leading-snug line-clamp-2 mb-1.5"><?= htmlspecialchars($p['nama_produk']) ?></h3>
                    <p class="text-[11px] text-zcMut leading-relaxed line-clamp-2 mb-3 flex-1"><?= htmlspecialchars($p['deskripsi'] ?? 'Produk medis berkualitas tinggi dan bersertifikat resmi.') ?></p>

                    <!-- Conversion Info -->
                    <div class="flex items-center justify-between text-[11px] bg-amber-50 border border-amber-200 rounded-lg px-2.5 py-1.5 mb-3">
                        <div class="flex items-center gap-1.5 text-amber-800 font-medium">
                            <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                            1 <?= htmlspecialchars($satBesar) ?>
                        </div>
                        <span class="text-amber-600">=</span>
                        <span class="font-bold text-amber-800"><?= $rasio ?> <?= htmlspecialchars($satKecil) ?></span>
                    </div>

                    <!-- Price -->
                    <div class="flex items-baseline justify-between mb-3">
                        <div>
                            <span class="text-lg font-extrabold text-zcTxt">Rp <?= number_format($harga, 0, ',', '.') ?></span>
                            <span class="text-[11px] text-zcMut ml-1">/ <?= htmlspecialchars($satBesar) ?></span>
                        </div>
                        <span class="text-[10px] text-zcMut"><?= intval($p['berat_gram']) ?> gr</span>
                    </div>

                    <!-- Add to Cart -->
                    <?php if ($stokBox >= 1): ?>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-[11px] text-zcMut shrink-0">Jml:</span>
                            <div class="flex items-center border border-zcBrd rounded-lg overflow-hidden flex-1">
                                <button type="button" onclick="adjustQty(<?= $p['id'] ?>, -1, <?= $stokBox ?>)"
                                    class="w-8 h-8 flex items-center justify-center bg-slate-50 hover:bg-slate-100 text-zcTxt font-bold text-sm transition border-r border-zcBrd">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M5 12h14"/></svg>
                                </button>
                                <input type="number" id="qty_<?= $p['id'] ?>" value="1" min="1" max="<?= $stokBox ?>" readonly
                                    class="flex-1 text-center text-xs font-bold py-1 bg-white focus:outline-none">
                                <button type="button" onclick="adjustQty(<?= $p['id'] ?>, 1, <?= $stokBox ?>)"
                                    class="w-8 h-8 flex items-center justify-center bg-slate-50 hover:bg-slate-100 text-zcTxt font-bold text-sm transition border-l border-zcBrd">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                                </button>
                            </div>
                            <span class="text-[10px] text-zcMut shrink-0">Max: <?= $stokBox ?></span>
                        </div>
                        <button onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes($p['nama_produk']) ?>', <?= $harga ?>, <?= intval($p['berat_gram']) ?>, '<?= addslashes($p['gambar'] ?? '') ?>', '<?= addslashes($satBesar) ?>', <?= $rasio ?>, <?= $stokBox ?>)"
                            class="w-full flex items-center justify-center gap-2 text-xs font-bold py-2.5 px-3 rounded-xl border transition bg-zc hover:bg-zcHv text-white border-zc shadow-sm">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.55L23 6H6"/></svg>
                            Tambah ke Keranjang
                        </button>
                    <?php else: ?>
                        <div class="w-full flex items-center justify-center gap-2 text-xs font-bold py-2.5 px-3 rounded-xl border bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
                            Stok Tidak Tersedia
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Empty state -->
            <div id="no-results" class="hidden col-span-full py-16 text-center">
                <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <p class="text-sm font-semibold text-zcMut">Tidak ada produk ditemukan.</p>
                <p class="text-xs text-slate-400 mt-1">Coba ubah kata kunci atau pilih kategori lain.</p>
            </div>
        </div>
    </main>

    <!-- =========================================================== -->
    <!-- FOOTER                                                        -->
    <!-- =========================================================== -->
    <footer class="bg-slate-900 text-slate-400 mt-12 py-8">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-zc flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                </div>
                <span class="text-white font-semibold text-sm"><?= htmlspecialchars($namaToko) ?></span>
            </div>
            <p class="text-xs">&copy; <?= date('Y') ?> <?= htmlspecialchars($namaToko) ?>. Distributor Resmi Alkes &amp; Medis.</p>
            <div class="flex gap-4 text-xs">
                <a href="login_customer.php" class="hover:text-white transition">Login Pelanggan</a>
                <a href="register.php" class="hover:text-white transition">Daftar Akun</a>
            </div>
        </div>
    </footer>

    <!-- =========================================================== -->
    <!-- TOAST NOTIFICATION                                           -->
    <!-- =========================================================== -->
    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden">
        <div class="bg-emerald-600 text-white text-xs font-semibold px-5 py-3 rounded-xl shadow-lg flex items-center gap-2.5 max-w-xs">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span id="toast-msg">Ditambahkan ke keranjang!</span>
        </div>
    </div>

    <!-- =========================================================== -->
    <!-- JAVASCRIPT                                                    -->
    <!-- =========================================================== -->
    <script>
    // ─── Product Metadata (stok limit per product from server) ───
    const productMeta = <?= json_encode($productMeta) ?>;
    
    // ─── Cart Functions ───────────────────────────────────────────
    function getCart() { return JSON.parse(localStorage.getItem('zencare_cart') || '[]'); }
    function saveCart(cart) {
        localStorage.setItem('zencare_cart', JSON.stringify(cart));
        updateCartBadge();
    }

    function updateCartBadge() {
        const cart = getCart();
        const total = cart.reduce((s, i) => s + i.qty, 0);
        const badge = document.getElementById('cart-badge');
        if (badge) {
            const old = parseInt(badge.innerText);
            badge.innerText = total;
            if (total !== old) {
                badge.classList.add('pop');
                setTimeout(() => badge.classList.remove('pop'), 300);
            }
        }
    }

    function adjustQty(id, delta, maxStokBox) {
        const input = document.getElementById('qty_' + id);
        let val = parseInt(input.value) || 1;

        // Calculate already-in-cart qty for this product
        const cart = getCart();
        const existing = cart.find(i => i.id === id);
        const inCart = existing ? existing.qty : 0;

        val = Math.max(1, val + delta);
        // Can't select more than remaining available
        const remaining = maxStokBox - inCart;
        if (remaining <= 0) {
            showToast('Semua stok sudah ada di keranjang!', 'error');
            return;
        }
        val = Math.min(val, remaining);
        input.value = val;
    }

    function addToCart(id, name, price, weight, image, satBesar, rasio, maxStokBox) {
        const qtyInput = document.getElementById('qty_' + id);
        let qty = parseInt(qtyInput?.value) || 1;

        // Check current cart qty for this product
        const cart = getCart();
        const existing = cart.find(i => i.id === id);
        const inCart = existing ? existing.qty : 0;

        // Validate: total (inCart + new qty) must not exceed stokBox
        if (inCart + qty > maxStokBox) {
            const remaining = maxStokBox - inCart;
            if (remaining <= 0) {
                showToast('Stok ' + satBesar + ' habis! Semua sudah di keranjang.', 'error');
            } else {
                showToast('Stok hanya tersisa ' + remaining + ' ' + satBesar + ' lagi!', 'error');
                if (qtyInput) qtyInput.value = remaining;
            }
            return;
        }

        if (existing) {
            existing.qty += qty;
        } else {
            cart.push({
                id: id,
                name: name,
                price: price,
                weight: weight,
                image: image,
                qty: qty,
                satuan_besar: satBesar,
                satuan_label: satBesar,
                rasio_konversi: rasio,
                maxStokBox: maxStokBox,
                stokBox: maxStokBox
            });
        }

        saveCart(cart);
        if (qtyInput) qtyInput.value = 1;
        showToast(qty + ' ' + satBesar + ' "' + name.substring(0, 30) + '..." ditambahkan!', 'success');
    }

    function showToast(msg, type = 'success') {
        const toast = document.getElementById('toast');
        const toastInner = toast.querySelector('div');
        const toastMsg = document.getElementById('toast-msg');
        toastMsg.innerText = msg;
        if (type === 'error') {
            toastInner.className = 'bg-rose-600 text-white text-xs font-semibold px-5 py-3 rounded-xl shadow-lg flex items-center gap-2.5 max-w-xs';
        } else {
            toastInner.className = 'bg-emerald-600 text-white text-xs font-semibold px-5 py-3 rounded-xl shadow-lg flex items-center gap-2.5 max-w-xs';
        }
        toast.classList.remove('hidden');
        clearTimeout(window._toastTimer);
        window._toastTimer = setTimeout(() => toast.classList.add('hidden'), 3000);
    }

    // ─── Category Filter ──────────────────────────────────────────
    function filterCat(cat) {
        document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.cat-tab[data-cat="' + cat + '"]').forEach(t => t.classList.add('active'));
        
        let visibleCount = 0;
        document.querySelectorAll('.product-card').forEach(card => {
            const matchCat = cat === 'semua' || card.dataset.cat === cat;
            const q = document.getElementById('store_search')?.value.toLowerCase() || '';
            const matchSearch = !q || card.dataset.name.includes(q);
            const show = matchCat && matchSearch;
            card.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });
        document.getElementById('no-results').style.display = visibleCount === 0 ? '' : 'none';
    }

    // ─── Search Filter ────────────────────────────────────────────
    function filterProducts() {
        const q = document.getElementById('store_search').value.toLowerCase();
        const activeCat = document.querySelector('.cat-tab.active')?.dataset.cat || 'semua';
        let visibleCount = 0;
        document.querySelectorAll('.product-card').forEach(card => {
            const matchSearch = !q || card.dataset.name.includes(q);
            const matchCat = activeCat === 'semua' || card.dataset.cat === activeCat;
            const show = matchSearch && matchCat;
            card.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });
        document.getElementById('no-results').style.display = visibleCount === 0 ? '' : 'none';
    }

    // ─── Sort ─────────────────────────────────────────────────────
    function sortProducts() {
        const val = document.getElementById('sort_select').value;
        const grid = document.getElementById('product-grid');
        const cards = [...grid.querySelectorAll('.product-card')];
        cards.sort((a, b) => {
            if (val === 'price_asc')  return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
            if (val === 'price_desc') return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
            if (val === 'name_asc')   return a.dataset.name.localeCompare(b.dataset.name);
            return parseInt(a.dataset.id) - parseInt(b.dataset.id);
        });
        cards.forEach(c => grid.appendChild(c));
    }

    // ─── Hero Slider ──────────────────────────────────────────────
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');
    const dots   = document.querySelectorAll('.slide-dot');

    function goToSlide(n) {
        slides.forEach(s => s.classList.remove('active'));
        dots.forEach(d => d.classList.remove('bg-white'));
        currentSlide = (n + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        dots[currentSlide]?.classList.add('bg-white');
    }
    function nextSlide() { goToSlide(currentSlide + 1); }
    function prevSlide() { goToSlide(currentSlide - 1); }
    
    // Auto-play
    let autoSlide = setInterval(nextSlide, 5000);
    document.getElementById('hero-slider').addEventListener('mouseenter', () => clearInterval(autoSlide));
    document.getElementById('hero-slider').addEventListener('mouseleave', () => { autoSlide = setInterval(nextSlide, 5000); });
    goToSlide(0);

    // Init
    document.addEventListener('DOMContentLoaded', updateCartBadge);
    </script>
</body>
</html>