<?php
// File: ecommerce/index.php — ZenCare Medical Store (Redesign Toko Online Modern & Otentik)
$pageTitle = "Toko Alat Kesehatan & Obat";
require_once __DIR__ . '/header.php';

// Ambil seluruh produk online aktif
$stmtProduk = $pdo->prepare("
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
           v.tampil_di_online, v.satuan_besar, v.satuan_kecil, v.rasio_konversi
    FROM produk_variasi v
    JOIN produk_induk i ON v.id_produk_induk = i.id
    LEFT JOIN stok_toko sc ON sc.id_variasi = v.id 
    WHERE v.is_active = 1 AND i.is_active = 1 AND v.tampil_di_online = 1
    ORDER BY i.kategori ASC, i.nama_produk ASC, v.id ASC
");
$stmtProduk->execute();
$products = $stmtProduk->fetchAll();

// Hitung total produk per kategori
$catCounts = [
    'Obat' => 0,
    'Alat Kesehatan' => 0
];
foreach ($products as $p) {
    if (isset($catCounts[$p['kategori']])) {
        $catCounts[$p['kategori']]++;
    } else {
        $catCounts[$p['kategori']] = 1;
    }
}

// Kumpulkan daftar Produk Induk unik untuk filter dropdown
$parentProducts = [];
foreach ($products as $p) {
    $idInduk = $p['id_produk_induk'];
    if (!isset($parentProducts[$idInduk])) {
        $parentProducts[$idInduk] = [
            'id' => $idInduk,
            'nama' => $p['nama_induk'],
            'kategori' => $p['kategori']
        ];
    }
}
usort($parentProducts, fn($a, $b) => strcmp($a['nama'], $b['nama']));

// Helper untuk mengekstrak gambar pertama dari multi-gambar (koma)
function getFirstProductImage($rawGambar) {
    if (empty($rawGambar)) return '';
    $arr = array_filter(array_map('trim', explode(',', $rawGambar)));
    $first = $arr[0] ?? '';
    if (empty($first)) return '';
    if (str_starts_with($first, 'http')) return $first;
    if (str_contains($first, '/')) return '../' . $first;
    return '../assets/img/produk/' . $first;
}

// Ambil sampel produk bergambar untuk showcase slide
$imgProds = array_values(array_filter($products, fn($p) => !empty($p['gambar'])));
$alkesProd = array_values(array_filter($imgProds, fn($p) => $p['kategori'] === 'Alat Kesehatan'))[0] ?? ($imgProds[0] ?? null);
$obatProd = array_values(array_filter($imgProds, fn($p) => $p['kategori'] === 'Obat'))[0] ?? ($imgProds[1] ?? null);
$leadProd = $imgProds[0] ?? ($products[0] ?? null);

$initCategory = $_GET['kategori'] ?? 'all';
?>

<style>
/* ── Hero Slideshow ── */
.hero-slide { display: none; opacity: 0; transition: opacity .6s ease; }
.hero-slide.active { display: flex; opacity: 1; }
.slide-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,.35); cursor: pointer; transition: all .25s; }
.slide-dot.active { background: #fff; width: 28px; border-radius: 4px; }

/* ── Product Card ── */
.prod-card {
    transition: transform .22s cubic-bezier(.4,0,.2,1), box-shadow .22s;
    border: 1.5px solid #e2e8f0;
}
.prod-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 36px rgba(15,45,90,.09);
    border-color: #1a75d2;
}
.prod-card:hover .card-img img { transform: scale(1.05); }
.card-img img { transition: transform .3s ease; }

/* ── Filter Pills ── */
.cat-filter-btn { transition: all .2s; }
.cat-filter-btn.active {
    background-color: #1a75d2 !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(26,117,210,.25);
}
</style>

<main class="flex-1 w-full bg-[#f8fafc]">

    <!-- ============================================================ -->
    <!-- HERO PROMOTIONAL BANNER SLIDESHOW (Commercial E-Commerce)     -->
    <!-- ============================================================ -->
    <section class="relative overflow-hidden bg-gradient-to-br from-[#0f2d5a] via-[#154689] to-[#1a75d2]" style="min-height:360px;">
        <div class="absolute inset-0 opacity-[.05]" style="background-image:radial-gradient(circle,white 1px,transparent 1px);background-size:24px 24px;"></div>

        <!-- SLIDE 1: PUSAT ALKES & OBAT RESMI -->
        <div class="hero-slide active absolute inset-0 flex flex-col md:flex-row items-center justify-between gap-6 px-6 sm:px-12 lg:px-20 py-10" data-slide="0">
            <div class="flex-1 text-white z-10 max-w-xl mb-4 md:mb-0">
                <div class="flex items-center gap-2 mb-3 flex-wrap">
                    <span class="inline-flex items-center px-3 py-1 rounded-full border border-white/20 bg-white/10 text-xs font-bold uppercase tracking-wider text-blue-100 backdrop-blur-md">
                        Distributor Resmi &bull; Toko Terpercaya
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-900/50 border border-blue-400/30 text-[11px] font-semibold text-blue-200">
                        100% Produk Terjamin Asli
                    </span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black leading-tight tracking-tight mb-3">
                    Pusat Belanja Alat Kesehatan &amp; Obat
                </h1>
                <p class="text-blue-100/90 text-sm sm:text-base leading-relaxed mb-6 line-clamp-2 max-w-lg">
                    Menyediakan perlengkapan medis berstandar mutu dan obat-obatan resmi dengan harga grosir kompetitif serta jaminan segel pabrik.
                </p>
                <div class="flex items-center gap-3 flex-wrap">
                    <a href="#katalog" class="px-6 py-3 bg-white text-[#0f2d5a] font-extrabold text-sm rounded-xl hover:bg-blue-50 transition shadow-lg active:scale-95 flex items-center gap-2">
                        Mulai Belanja
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    <a href="cara_pembelian.php" class="px-5 py-3 bg-white/10 border border-white/25 text-white font-bold text-sm rounded-xl hover:bg-white/20 transition backdrop-blur-sm">
                        Cara Pembelian
                    </a>
                </div>
            </div>
            <div class="shrink-0 flex items-center justify-center md:w-80 lg:w-96 relative">
                <div class="relative bg-white/10 backdrop-blur-md p-4 sm:p-6 rounded-3xl border border-white/20 shadow-2xl flex items-center justify-center">
                    <?php 
                    $leadImg = $leadProd ? getFirstProductImage($leadProd['gambar']) : '';
                    if (!empty($leadImg)): 
                    ?>
                        <img src="<?= htmlspecialchars($leadImg) ?>" alt="<?= htmlspecialchars($leadProd['nama_induk']) ?>"
                             class="h-44 sm:h-52 lg:h-60 object-contain drop-shadow-2xl hover:scale-105 transition-transform duration-500">
                    <?php else: ?>
                        <div class="w-44 h-44 rounded-2xl bg-white/10 flex items-center justify-center text-white">
                            <svg class="w-16 h-16 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- SLIDE 2: ALAT KESEHATAN BERGARANSI -->
        <div class="hero-slide absolute inset-0 flex flex-col md:flex-row items-center justify-between gap-6 px-6 sm:px-12 lg:px-20 py-10" data-slide="1">
            <div class="flex-1 text-white z-10 max-w-xl mb-4 md:mb-0">
                <div class="flex items-center gap-2 mb-3 flex-wrap">
                    <span class="inline-flex items-center px-3 py-1 rounded-full border border-emerald-300/30 bg-emerald-900/40 text-xs font-bold uppercase tracking-wider text-emerald-200 backdrop-blur-md">
                        Diagnostik &bull; Perlengkapan Medis
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-900/50 border border-emerald-400/30 text-[11px] font-semibold text-emerald-200">
                        Garansi Mutu Terjamin
                    </span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black leading-tight tracking-tight mb-3">
                    Alat Kesehatan Akurat &amp; Teruji
                </h1>
                <p class="text-emerald-100/90 text-sm sm:text-base leading-relaxed mb-6 line-clamp-2 max-w-lg">
                    Tensimeter digital, termometer inframerah, kursi roda, dan kebutuhan medis esensial untuk klinik maupun penggunaan pribadi di rumah.
                </p>
                <div class="flex items-center gap-3 flex-wrap">
                    <button onclick="setCategoryFilter('Alat Kesehatan'); document.getElementById('katalog').scrollIntoView({behavior:'smooth'});" class="px-6 py-3 bg-white text-emerald-900 font-extrabold text-sm rounded-xl hover:bg-emerald-50 transition shadow-lg active:scale-95 flex items-center gap-2">
                        Lihat Alat Kesehatan
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </button>
                    <a href="https://wa.me/6281234567890?text=Halo%20ZenCare%20Medical,%20saya%20ingin%20konsultasi%20mengenai%20alat%20kesehatan" target="_blank" class="px-5 py-3 bg-white/10 border border-white/25 text-white font-bold text-sm rounded-xl hover:bg-white/20 transition backdrop-blur-sm">
                        Konsultasi Produk
                    </a>
                </div>
            </div>
            <div class="shrink-0 flex items-center justify-center md:w-80 lg:w-96 relative">
                <div class="relative bg-white/10 backdrop-blur-md p-4 sm:p-6 rounded-3xl border border-white/20 shadow-2xl flex items-center justify-center">
                    <?php 
                    $alkesImg = $alkesProd ? getFirstProductImage($alkesProd['gambar']) : '';
                    if (!empty($alkesImg)): 
                    ?>
                        <img src="<?= htmlspecialchars($alkesImg) ?>" alt="<?= htmlspecialchars($alkesProd['nama_induk']) ?>"
                             class="h-44 sm:h-52 lg:h-60 object-contain drop-shadow-2xl hover:scale-105 transition-transform duration-500">
                    <?php else: ?>
                        <div class="w-44 h-44 rounded-2xl bg-white/10 flex items-center justify-center text-white">
                            <svg class="w-16 h-16 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-3-3v6m-7 4h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- SLIDE 3: OBAT RESMI BERMUTU -->
        <div class="hero-slide absolute inset-0 flex flex-col md:flex-row items-center justify-between gap-6 px-6 sm:px-12 lg:px-20 py-10" data-slide="2">
            <div class="flex-1 text-white z-10 max-w-xl mb-4 md:mb-0">
                <div class="flex items-center gap-2 mb-3 flex-wrap">
                    <span class="inline-flex items-center px-3 py-1 rounded-full border border-blue-300/30 bg-blue-950/40 text-xs font-bold uppercase tracking-wider text-blue-200 backdrop-blur-md">
                        Obat &bull; Obat Bebas &amp; Terbatas
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-900/50 border border-blue-400/30 text-[11px] font-semibold text-blue-200">
                        Kedaluwarsa Aman Terjaga
                    </span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black leading-tight tracking-tight mb-3">
                    Obat-Obatan Resmi &amp; Tersegel
                </h1>
                <p class="text-blue-100/90 text-sm sm:text-base leading-relaxed mb-6 line-clamp-2 max-w-lg">
                    Setiap kemasan disimpan dalam kondisi higienis dengan kontrol tanggal kedaluwarsa ketat. Tersedia opsi Ambil di Toko tanpa antre &amp; bebas ongkir.
                </p>
                <div class="flex items-center gap-3 flex-wrap">
                    <button onclick="setCategoryFilter('Obat'); document.getElementById('katalog').scrollIntoView({behavior:'smooth'});" class="px-6 py-3 bg-white text-blue-950 font-extrabold text-sm rounded-xl hover:bg-blue-50 transition shadow-lg active:scale-95 flex items-center gap-2">
                        Lihat Obat-obatan
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </button>
                    <a href="cara_pembelian.php" class="px-5 py-3 bg-white/10 border border-white/25 text-white font-bold text-sm rounded-xl hover:bg-white/20 transition backdrop-blur-sm">
                        Panduan Pengiriman
                    </a>
                </div>
            </div>
            <div class="shrink-0 flex items-center justify-center md:w-80 lg:w-96 relative">
                <div class="relative bg-white/10 backdrop-blur-md p-4 sm:p-6 rounded-3xl border border-white/20 shadow-2xl flex items-center justify-center">
                    <?php 
                    $obatImg = $obatProd ? getFirstProductImage($obatProd['gambar']) : '';
                    if (!empty($obatImg)): 
                    ?>
                        <img src="<?= htmlspecialchars($obatImg) ?>" alt="<?= htmlspecialchars($obatProd['nama_induk']) ?>"
                             class="h-44 sm:h-52 lg:h-60 object-contain drop-shadow-2xl hover:scale-105 transition-transform duration-500">
                    <?php else: ?>
                        <div class="w-44 h-44 rounded-2xl bg-white/10 flex items-center justify-center text-white">
                            <svg class="w-16 h-16 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Slide Navigation Dots & Arrows -->
        <div class="absolute bottom-5 left-0 right-0 flex items-center justify-center gap-2 z-20">
            <span class="slide-dot active" onclick="goSlide(0)"></span>
            <span class="slide-dot" onclick="goSlide(1)"></span>
            <span class="slide-dot" onclick="goSlide(2)"></span>
        </div>
        <button onclick="prevSlide()" class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 bg-white/10 hover:bg-white/25 border border-white/20 rounded-full flex items-center justify-center text-white transition backdrop-blur-sm">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <button onclick="nextSlide()" class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 bg-white/10 hover:bg-white/25 border border-white/20 rounded-full flex items-center justify-center text-white transition backdrop-blur-sm">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </button>
    </section>

    <!-- ============================================================ -->
    <!-- TRUST BADGES (Keunggulan Belanja di ZenCare)                  -->
    <!-- ============================================================ -->
    <div class="bg-white border-b border-slate-200 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 py-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1a75d2] border border-blue-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-800 leading-snug">100% Produk Original</p>
                        <p class="text-[11px] text-slate-500">Segel Pabrik &amp; Mutu Terjaga</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-800 leading-snug">Pengiriman Nasional</p>
                        <p class="text-[11px] text-slate-500">Ekspedisi JNE, POS &amp; TIKI</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-700 border border-teal-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-800 leading-snug">Ambil di Toko</p>
                        <p class="text-[11px] text-slate-500">Bebas Biaya Ongkos Kirim</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 border border-amber-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-800 leading-snug">Pembayaran Aman</p>
                        <p class="text-[11px] text-slate-500">Midtrans &amp; Transfer Bank</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- KATALOG PRODUK E-COMMERCE (Filter Produk Induk & Paginasi)   -->
    <!-- ============================================================ -->
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-10">
        
        <section id="katalog" class="scroll-mt-6">
            
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 pb-4 border-b border-slate-200">
                <div>
                    <div class="flex items-center gap-2.5 mb-1">
                        <span class="w-2.5 h-6 rounded-full bg-[#1a75d2]"></span>
                        <h2 class="text-2xl font-black text-[#0f2d5a] tracking-tight">Katalog Produk</h2>
                    </div>
                    <p class="text-xs md:text-sm text-slate-500">Pilih obat dan alat kesehatan resmi dengan kemasan grosir terjamin</p>
                </div>
                
                <!-- Display Controls: Limit Per Halaman & Urutan -->
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex items-center gap-1.5 text-xs">
                        <label for="per_page_select" class="font-bold text-slate-600">Tampilkan:</label>
                        <select id="per_page_select" onchange="changePerPage(this.value)" class="text-xs border border-slate-200 rounded-xl px-2.5 py-2 bg-white font-bold text-[#0f2d5a] focus:outline-none focus:border-[#1a75d2] shadow-2xs">
                            <option value="8">8 Produk</option>
                            <option value="12" selected>12 Produk</option>
                            <option value="16">16 Produk</option>
                            <option value="20">20 Produk</option>
                            <option value="all">Semua Produk</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs">
                        <label for="sort_select" class="font-bold text-slate-600">Urutkan:</label>
                        <select id="sort_select" onchange="sortAndFilterProducts()" class="text-xs border border-slate-200 rounded-xl px-2.5 py-2 bg-white font-bold text-[#0f2d5a] focus:outline-none focus:border-[#1a75d2] shadow-2xs">
                            <option value="default">Urutan Standar</option>
                            <option value="price_asc">Harga Terendah</option>
                            <option value="price_desc">Harga Tertinggi</option>
                            <option value="name_asc">Nama (A–Z)</option>
                            <option value="stok_desc">Stok Terbanyak</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Filter Card: Kategori Pills + Filter Produk Induk + Search -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs mb-8 space-y-4">
                <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
                    
                    <!-- Category Pills -->
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar flex-wrap text-xs">
                        <button type="button" onclick="setCategoryFilter('all')" id="cat_pill_all" class="cat-filter-btn px-4 py-2 rounded-xl font-bold transition shadow-xs active">
                            Semua Produk (<?= count($products) ?>)
                        </button>
                        <button type="button" onclick="setCategoryFilter('Obat')" id="cat_pill_Obat" class="cat-filter-btn px-4 py-2 rounded-xl font-bold transition bg-slate-100 text-slate-600 hover:bg-slate-200">
                            Obat-obatan (<?= $catCounts['Obat'] ?? 0 ?>)
                        </button>
                        <button type="button" onclick="setCategoryFilter('Alat Kesehatan')" id="cat_pill_Alat_Kesehatan" class="cat-filter-btn px-4 py-2 rounded-xl font-bold transition bg-slate-100 text-slate-600 hover:bg-slate-200">
                            Alat Kesehatan (<?= $catCounts['Alat Kesehatan'] ?? 0 ?>)
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full lg:w-72">
                        <input type="text" id="catalog_search" oninput="onSearchChange(this.value)" placeholder="Cari produk, variasi, SKU..." class="w-full text-xs pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#1a75d2] bg-slate-50/70">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>

                <!-- Secondary Filter: Filter Berdasarkan Produk Induk -->
                <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                        <span class="font-bold text-slate-700 shrink-0">Filter Produk Induk:</span>
                        <select id="filter_induk" onchange="onParentFilterChange(this.value)" class="text-xs border border-slate-200 rounded-xl px-3 py-1.5 bg-slate-50 font-semibold text-slate-700 focus:outline-none focus:border-[#1a75d2] w-full sm:w-64">
                            <option value="all">Semua Produk Induk</option>
                            <?php foreach ($parentProducts as $pi): ?>
                            <option value="<?= $pi['id'] ?>" data-kategori="<?= htmlspecialchars($pi['kategori']) ?>">
                                <?= htmlspecialchars($pi['nama']) ?> (<?= htmlspecialchars($pi['kategori']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" id="reset_filter_btn" onclick="resetFilters()" class="hidden px-2.5 py-1.5 text-xs text-rose-600 hover:bg-rose-50 rounded-lg font-bold border border-rose-200 transition">
                            Reset Filter
                        </button>
                    </div>
                    
                    <div id="filter_result_count" class="text-slate-400 font-medium">
                        Menampilkan <strong class="text-slate-700" id="count_display"><?= count($products) ?></strong> variasi produk
                    </div>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="product-grid">
                <?php foreach ($products as $p):
                    $rasio    = intval($p['rasio_konversi']) ?: 1;
                    $stokBox  = ($p['kategori'] === 'Obat')
                        ? (int) floor(intval($p['stok_batch_utuh']) / $rasio)
                        : (int) floor(intval($p['stok_sistem']) / $rasio);
                    $harga    = floatval($p['harga_jual']);
                    $hargaEcr = floatval($p['harga_eceran']);
                    $satB     = htmlspecialchars($p['satuan_besar'] ?: 'Box');
                    $satK     = htmlspecialchars($p['satuan_kecil'] ?: 'Pcs');
                    $desc     = trim($p['deskripsi'] ?? '');
                    $descSh   = $desc ? mb_strimwidth($desc, 0, 75, '…') : 'Produk medis berkualitas dengan jaminan mutu resmi.';

                    if ($stokBox <= 0) {
                        $stokBadge = 'bg-rose-50 text-rose-700 border-rose-200';
                        $stokLbl   = 'Habis';
                    } elseif ($stokBox <= 5) {
                        $stokBadge = 'bg-amber-50 text-amber-800 border-amber-200';
                        $stokLbl   = "Tersisa $stokBox $satB";
                    } else {
                        $stokBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        $stokLbl   = "Stok: $stokBox $satB";
                    }
                ?>
                <div class="prod-card group bg-white rounded-2xl overflow-hidden flex flex-col"
                     data-id="<?= $p['id'] ?>"
                     data-induk-id="<?= $p['id_produk_induk'] ?>"
                     data-kategori="<?= htmlspecialchars($p['kategori']) ?>"
                     data-name="<?= strtolower(htmlspecialchars($p['nama_produk'])) ?>"
                     data-sku="<?= strtolower(htmlspecialchars($p['sku_variasi'])) ?>"
                     data-price="<?= $harga ?>"
                     data-stok="<?= $stokBox ?>">

                    <!-- Product Image & Header Badges -->
                    <?php 
                        $firstImg = getFirstProductImage($p['gambar'] ?? '');
                        $allImgs = array_filter(array_map('trim', explode(',', $p['gambar'] ?? '')));
                        $totalImgs = count($allImgs);
                    ?>
                    <a href="produk.php?id=<?= $p['id'] ?>" class="card-img relative block bg-slate-50/70 border-b border-slate-100 overflow-hidden" style="height:210px;">
                        <?php if (!empty($firstImg)): ?>
                            <img src="<?= htmlspecialchars($firstImg) ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>"
                                 class="w-full h-full object-contain p-4 mix-blend-multiply transition-transform duration-300 hover:scale-105"
                                 onerror="this.onerror=null; this.src='../assets/img/no-image.png';">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-slate-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12h6M12 9v6"/></svg>
                            </div>
                        <?php endif; ?>

                        <!-- Category Badge (Clean, Non-blinking) -->
                        <span class="absolute top-3 left-3 px-2.5 py-1 bg-white/95 text-[10px] font-bold rounded-lg border shadow-2xs <?= $p['kategori'] === 'Obat' ? 'text-indigo-700 border-indigo-100' : 'text-teal-700 border-teal-100' ?>">
                            <?= htmlspecialchars($p['kategori']) ?>
                        </span>

                        <!-- Stock Badge (Clean, Non-blinking) -->
                        <span class="absolute top-3 right-3 px-2.5 py-1 text-[10px] font-bold rounded-lg border shadow-2xs <?= $stokBadge ?>">
                            <?= $stokLbl ?>
                        </span>

                        <!-- Multi-image indicator badge -->
                        <?php if ($totalImgs > 1): ?>
                            <span class="absolute bottom-2.5 right-2.5 px-2 py-0.5 bg-slate-900/75 backdrop-blur-sm text-white text-[9px] font-bold rounded-md flex items-center gap-1 shadow-2xs">
                                <svg class="w-2.5 h-2.5 text-blue-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                                <?= $totalImgs ?> Foto
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- Card Body -->
                    <div class="p-4 flex flex-col flex-1">
                        <a href="produk.php?id=<?= $p['id'] ?>" class="block mb-2.5">
                            <h3 class="text-sm font-extrabold text-[#1e293b] leading-snug line-clamp-2 group-hover:text-[#1a75d2] transition-colors">
                                <?= htmlspecialchars($p['nama_induk']) ?>
                            </h3>
                            <span class="text-xs text-[#64748b]"><?= htmlspecialchars($p['nama_variasi']) ?></span>
                        </a>

                        <!-- Specs Mini Info -->
                        <div class="rounded-xl bg-slate-50 border border-slate-100 divide-y divide-slate-100 mb-4 text-xs">
                            <div class="flex gap-2 px-3 py-1.5">
                                <span class="text-[#64748b] w-[70px] shrink-0 font-medium">Kemasan</span>
                                <span class="font-bold text-[#1e293b]">1 <?= $satB ?> = <?= $rasio ?> <?= $satK ?></span>
                            </div>
                            <div class="flex gap-2 px-3 py-1.5">
                                <span class="text-[#64748b] w-[70px] shrink-0 font-medium">SKU</span>
                                <span class="font-mono text-[#475569] text-[11px]"><?= htmlspecialchars($p['sku_variasi']) ?></span>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="mb-4 mt-auto">
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-lg font-black text-[#1e293b]">Rp <?= number_format($harga, 0, ',', '.') ?></span>
                                <span class="text-xs text-[#64748b] font-medium">/ <?= $satB ?></span>
                            </div>
                            <?php if ($hargaEcr > 0 && $hargaEcr != $harga / $rasio): ?>
                            <div class="text-[10px] text-slate-400 mt-0.5">~Rp <?= number_format($hargaEcr, 0, ',', '.') ?> / <?= $satK ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Controls -->
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
                            <span class="text-[10px] text-slate-400 shrink-0">Maks. <?= $stokBox ?></span>
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
            </div>

            <!-- Empty Search / Filter State -->
            <div id="empty_search_state" class="hidden py-16 text-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 mx-auto mb-3 flex items-center justify-center text-slate-400">
                    <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                </div>
                <h4 class="text-base font-bold text-slate-700 mb-1">Tidak ada produk yang cocok</h4>
                <p class="text-xs text-slate-400 mb-4">Coba sesuaikan kata kunci pencarian atau ubah filter kategori/produk induk.</p>
                <button type="button" onclick="resetFilters()" class="px-4 py-2 bg-[#1a75d2] text-white rounded-xl text-xs font-bold hover:bg-[#0f2d5a] transition">
                    Kembalikan Semua Produk
                </button>
            </div>

            <!-- Pagination Controls (Next / Back) -->
            <div id="pagination_container" class="mt-8 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
                <span id="page_info" class="text-slate-500 font-medium">
                    Menampilkan 1 – 12 dari <?= count($products) ?> produk
                </span>
                <div id="pagination_buttons" class="flex items-center gap-1.5 flex-wrap">
                    <!-- Populated dynamically by JS -->
                </div>
            </div>

        </section>

    </div>
</main>

<script>
// ── Slideshow Controller ───────────────────────────────────────────
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
    if (slides.length > 1) slideTimer = setInterval(() => goSlide(curSlide + 1), 5000);
}
if (slides.length > 1) restartTimer();

// ── Product Catalog Controller (Filter Induk, Limit & Pagination) ──
let currentCategory = '<?= $initCategory !== "all" ? addslashes($initCategory) : "all" ?>';
let currentParentId = 'all';
let currentSearch   = '';
let currentSort     = 'default';
let currentPage     = 1;
let perPage         = 12;

function setCategoryFilter(cat) {
    currentCategory = cat;
    currentPage = 1;

    // Update active pill styling
    document.querySelectorAll('.cat-filter-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-[#1a75d2]', 'text-white', 'shadow-xs');
        btn.classList.add('bg-slate-100', 'text-slate-600');
    });

    let activeBtnId = (cat === 'all') ? 'cat_pill_all' : (cat === 'Obat' ? 'cat_pill_Obat' : 'cat_pill_Alat_Kesehatan');
    let activeBtn = document.getElementById(activeBtnId);
    if (activeBtn) {
        activeBtn.classList.remove('bg-slate-100', 'text-slate-600');
        activeBtn.classList.add('active');
    }

    // Filter opsi dropdown produk induk sesuai kategori
    const indukSelect = document.getElementById('filter_induk');
    Array.from(indukSelect.options).forEach(opt => {
        if (opt.value === 'all') return;
        const optCat = opt.dataset.kategori;
        if (cat === 'all' || optCat === cat) {
            opt.style.display = '';
        } else {
            opt.style.display = 'none';
        }
    });

    // Reset produk induk jika saat ini tidak sesuai kategori
    const curOpt = indukSelect.selectedOptions[0];
    if (curOpt && curOpt.value !== 'all' && cat !== 'all' && curOpt.dataset.kategori !== cat) {
        indukSelect.value = 'all';
        currentParentId = 'all';
    }

    renderCatalog();
}

function onParentFilterChange(val) {
    currentParentId = val;
    currentPage = 1;
    renderCatalog();
}

function onSearchChange(val) {
    currentSearch = val.trim().toLowerCase();
    currentPage = 1;
    renderCatalog();
}

function onSortChange() {
    currentSort = document.getElementById('sort_select').value;
    renderCatalog();
}

function changePerPage(val) {
    perPage = val === 'all' ? 'all' : parseInt(val);
    currentPage = 1;
    renderCatalog();
}

function resetFilters() {
    currentSearch = '';
    document.getElementById('catalog_search').value = '';
    document.getElementById('filter_induk').value = 'all';
    currentParentId = 'all';
    setCategoryFilter('all');
}

function renderCatalog() {
    const grid = document.getElementById('product-grid');
    const cards = Array.from(grid.querySelectorAll('.prod-card'));
    const emptyState = document.getElementById('empty_search_state');
    const resetBtn = document.getElementById('reset_filter_btn');

    // Tampilkan tombol reset jika ada filter aktif
    if (currentCategory !== 'all' || currentParentId !== 'all' || currentSearch !== '') {
        resetBtn.classList.remove('hidden');
    } else {
        resetBtn.classList.add('hidden');
    }

    // 1. Filter
    let filtered = cards.filter(card => {
        const cat = card.dataset.kategori;
        const indukId = card.dataset.indukId;
        const name = card.dataset.name;
        const sku = card.dataset.sku || '';

        if (currentCategory !== 'all' && cat !== currentCategory) return false;
        if (currentParentId !== 'all' && indukId !== currentParentId) return false;
        if (currentSearch && !name.includes(currentSearch) && !sku.includes(currentSearch)) return false;
        return true;
    });

    // 2. Sort
    filtered.sort((a, b) => {
        if (currentSort === 'price_asc')  return +a.dataset.price - +b.dataset.price;
        if (currentSort === 'price_desc') return +b.dataset.price - +a.dataset.price;
        if (currentSort === 'name_asc')   return a.dataset.name.localeCompare(b.dataset.name);
        if (currentSort === 'stok_desc')  return +b.dataset.stok - +a.dataset.stok;
        return +a.dataset.id - +b.dataset.id;
    });

    const totalFiltered = filtered.length;
    document.getElementById('count_display').textContent = totalFiltered;

    if (totalFiltered === 0) {
        cards.forEach(c => c.style.display = 'none');
        emptyState.classList.remove('hidden');
        document.getElementById('pagination_container').classList.add('hidden');
        return;
    } else {
        emptyState.classList.add('hidden');
    }

    // 3. Pagination calculation
    let limit = (perPage === 'all') ? totalFiltered : perPage;
    let totalPages = Math.max(1, Math.ceil(totalFiltered / limit));
    if (currentPage > totalPages) currentPage = totalPages;

    let startIndex = (currentPage - 1) * limit;
    let endIndex   = Math.min(startIndex + limit, totalFiltered);

    // Sembunyikan semua kartu terlebih dahulu
    cards.forEach(c => c.style.display = 'none');

    // Tampilkan kartu yang masuk halaman aktif
    for (let i = startIndex; i < endIndex; i++) {
        filtered[i].style.display = 'flex';
        grid.appendChild(filtered[i]); // Re-order sesuai sort
    }

    // 4. Update Info & Tombol Paginasi
    const paginationContainer = document.getElementById('pagination_container');
    const pageInfo = document.getElementById('page_info');
    const pageButtons = document.getElementById('pagination_buttons');

    if (perPage === 'all' || totalPages <= 1) {
        paginationContainer.classList.add('hidden');
    } else {
        paginationContainer.classList.remove('hidden');
        pageInfo.textContent = `Menampilkan ${startIndex + 1} – ${endIndex} dari ${totalFiltered} produk`;

        let btnsHtml = '';

        // Tombol Sebelumnya
        if (currentPage > 1) {
            btnsHtml += `<button type="button" onclick="goToPage(${currentPage - 1})" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-semibold text-slate-700 transition">&larr; Sebelumnya</button>`;
        } else {
            btnsHtml += `<span class="px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-400 font-semibold cursor-not-allowed">&larr; Sebelumnya</span>`;
        }

        // Nomor Halaman
        for (let p = 1; p <= totalPages; p++) {
            if (p === 1 || p === totalPages || (p >= currentPage - 1 && p <= currentPage + 1)) {
                let activeCls = (p === currentPage) 
                    ? 'bg-[#1a75d2] border-[#1a75d2] text-white shadow-xs' 
                    : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50';
                btnsHtml += `<button type="button" onclick="goToPage(${p})" class="w-8 h-8 rounded-xl border text-xs font-bold transition ${activeCls}">${p}</button>`;
            } else if (p === 2 || p === totalPages - 1) {
                btnsHtml += `<span class="px-1 text-slate-400">&hellip;</span>`;
            }
        }

        // Tombol Selanjutnya
        if (currentPage < totalPages) {
            btnsHtml += `<button type="button" onclick="goToPage(${currentPage + 1})" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-semibold text-slate-700 transition">Selanjutnya &rarr;</button>`;
        } else {
            btnsHtml += `<span class="px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-400 font-semibold cursor-not-allowed">Selanjutnya &rarr;</span>`;
        }

        pageButtons.innerHTML = btnsHtml;
    }
}

function goToPage(page) {
    currentPage = page;
    renderCatalog();
    document.getElementById('katalog').scrollIntoView({ behavior: 'smooth' });
}

// Inisialisasi saat halaman selesai dimuat
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($initCategory !== 'all'): ?>
    setCategoryFilter('<?= addslashes($initCategory) ?>');
    <?php else: ?>
    renderCatalog();
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
