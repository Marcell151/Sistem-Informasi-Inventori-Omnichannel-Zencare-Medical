<?php
// File: ecommerce/index.php — ZenCare Medical Store (Pharmify Editorial Redesign)
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

// Cari gambar sampel berdasarkan kata kunci nama produk untuk banner & kategori
function findSampleProductImg($products, array $keywords, $fallbackKategori = null) {
    foreach ($products as $p) {
        if (empty($p['gambar'])) continue;
        $haystack = strtolower($p['nama_produk']);
        foreach ($keywords as $kw) {
            if (str_contains($haystack, strtolower($kw))) {
                return getFirstProductImage($p['gambar']);
            }
        }
    }
    if ($fallbackKategori) {
        foreach ($products as $p) {
            if (!empty($p['gambar']) && $p['kategori'] === $fallbackKategori) {
                return getFirstProductImage($p['gambar']);
            }
        }
    }
    foreach ($products as $p) {
        if (!empty($p['gambar'])) return getFirstProductImage($p['gambar']);
    }
    return '';
}

// Ambil sampel produk bergambar untuk showcase slide
$imgProds  = array_values(array_filter($products, fn($p) => !empty($p['gambar'])));
$alkesProd = array_values(array_filter($imgProds, fn($p) => $p['kategori'] === 'Alat Kesehatan'))[0] ?? ($imgProds[0] ?? null);
$obatProd  = array_values(array_filter($imgProds, fn($p) => $p['kategori'] === 'Obat'))[0] ?? ($imgProds[1] ?? null);
$leadProd  = $imgProds[0] ?? ($products[0] ?? null);

// Gambar khusus untuk Bento Grid & Kategori
$imgSirup      = findSampleProductImg($products, ['sirup', 'syrup', 'obh', 'sanmol', 'mylanta'], 'Obat');
$imgVitamin    = findSampleProductImg($products, ['enervon', 'imboost', 'neurobion', 'sangobion', 'cdr', 'vitamin'], 'Obat');
$imgDiagnostik = findSampleProductImg($products, ['tensi', 'termometer', 'oximeter', 'easytouch', 'nesco'], 'Alat Kesehatan');
$imgTablet     = findSampleProductImg($products, ['amox', 'paracetamol', 'bodrex', 'panadol', 'promag'], 'Obat');
$imgBmhp       = findSampleProductImg($products, ['alkohol', 'betadine', 'masker', 'kasa', 'handscoon', 'hansaplast'], 'Alat Kesehatan');
$imgTerapi     = findSampleProductImg($products, ['nebulizer', 'kursi', 'stetoskop', 'timbangan'], 'Alat Kesehatan');

$initCategory = $_GET['kategori'] ?? 'all';
$initInduk    = $_GET['induk'] ?? 'all';
$initQuery    = trim($_GET['q'] ?? '');
?>

<style>
/* ── Pharmify Hero Slideshow ── */
.hero-slide { display: none; opacity: 0; transition: opacity .5s ease; }
.hero-slide.active { display: flex; opacity: 1; }
.slide-dot { width: 9px; height: 9px; border-radius: 50%; background: rgba(15, 45, 90, 0.22); cursor: pointer; transition: all .25s ease; }
.slide-dot.active { background: #0f2d5a; transform: scale(1.15); }

/* ── Pharmify Product Card ── */
.prod-card {
    transition: transform .25s ease, box-shadow .25s ease;
}
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

/* ── Pharmify Category Underline Tabs ── */
.cat-filter-btn {
    position: relative;
    padding-bottom: 10px;
    color: #64748b;
    font-weight: 600;
    transition: color .2s ease;
}
.cat-filter-btn:hover {
    color: #0f2d5a;
}
.cat-filter-btn.active {
    color: #0f2d5a !important;
    font-weight: 700;
}
.cat-filter-btn.active::after {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    bottom: -1px;
    height: 2.5px;
    background-color: #0f2d5a;
}
</style>

<main class="flex-1 w-full bg-white">

    <!-- ============================================================ -->
    <!-- 1. PHARMIFY CLINICAL HERO SLIDER                             -->
    <!-- ============================================================ -->
    <section class="relative overflow-hidden bg-gradient-to-r from-[#d8eefb] via-[#e7f4fc] to-[#d4ecf9] border-b border-[#cbe4f5]">
        <!-- Subtle Molecular Background Vector (Pharmify Signature) -->
        <svg class="absolute inset-0 w-full h-full pointer-events-none opacity-45" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 500" preserveAspectRatio="xMidYMid slice">
            <g stroke="#ffffff" stroke-width="2" fill="none">
                <polygon points="180,70 230,40 280,70 280,130 230,160 180,130" />
                <polygon points="280,70 330,40 380,70 380,130 330,160 280,130" />
                <polygon points="230,160 280,130 330,160 330,220 280,250 230,220" />
                <line x1="180" y1="70" x2="130" y2="45" />
                <circle cx="130" cy="45" r="5" fill="#ffffff" />
                <circle cx="380" cy="70" r="6" fill="#7dd3fc" stroke="none" />
                <circle cx="230" cy="160" r="5" fill="#38bdf8" stroke="none" />
                
                <polygon points="1050,60 1110,25 1170,60 1170,130 1110,165 1050,130" />
                <polygon points="1170,60 1230,25 1290,60 1290,130 1230,165 1170,130" />
                <polygon points="1110,165 1170,130 1230,165 1230,235 1170,270 1110,235" />
                <circle cx="1050" cy="60" r="6" fill="#38bdf8" stroke="none" />
                <circle cx="1290" cy="130" r="7" fill="#ffffff" />
                <circle cx="960" cy="360" r="14" stroke="#ffffff" stroke-width="2.5" />
                <circle cx="680" cy="95" r="10" fill="#bae6fd" stroke="none" />
                <circle cx="590" cy="370" r="8" fill="#ffffff" stroke="none" />
            </g>
        </svg>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative min-h-[500px] sm:min-h-[460px] md:min-h-[440px] flex items-center">
            
            <!-- SLIDE 1: LAYANAN KESEHATAN & OBAT GROSIR -->
            <div class="hero-slide active w-full flex-col md:flex-row items-center justify-between gap-6 md:gap-8 px-2 sm:px-4 lg:px-6 py-8 sm:py-10 md:py-12" data-slide="0">
                <div class="flex-1 z-10 max-w-xl text-center md:text-left">
                    <p class="text-[10px] sm:text-xs font-bold uppercase tracking-[0.18em] text-[#1a75d2] mb-2 sm:mb-3">
                        DISTRIBUTOR FARMASI &amp; ALAT KESEHATAN RESMI
                    </p>
                    <h1 class="text-2xl sm:text-3xl lg:text-[42px] font-extrabold text-[#0f2d5a] leading-[1.2] tracking-tight mb-3 sm:mb-4">
                        Peduli Kesehatan Keluarga Dengan Obat &amp; Alat Medis Terjamin
                    </h1>
                    <p class="text-[#334155] text-xs sm:text-sm lg:text-base leading-relaxed mb-6 max-w-lg mx-auto md:mx-0">
                        Menyediakan obat-obatan kemasan box utuh bersegel resmi dan peralatan medis terkalibrasi untuk kebutuhan apotek, klinik, maupun keluarga Anda.
                    </p>
                    <div class="flex items-center justify-center md:justify-start gap-2.5 sm:gap-3 flex-wrap">
                        <a href="#katalog" class="w-full sm:w-auto px-6 sm:px-7 py-3 sm:py-3.5 bg-[#0f2d5a] hover:bg-[#1a75d2] text-white font-bold text-xs uppercase tracking-widest transition text-center">
                            BELANJA SEKARANG
                        </a>
                        <a href="kategori.php" class="w-full sm:w-auto px-5 sm:px-6 py-3 sm:py-3.5 bg-white/85 hover:bg-white text-[#0f2d5a] border border-[#0f2d5a]/15 font-bold text-xs uppercase tracking-wider transition text-center">
                            LIHAT SEMUA KATEGORI
                        </a>
                    </div>
                </div>
                <div class="shrink-0 flex items-center justify-center md:w-[360px] lg:w-[440px] z-10">
                    <div class="relative flex items-center justify-center w-full h-48 sm:h-64 md:h-80">
                        <div class="absolute w-44 h-44 sm:w-60 sm:h-60 md:w-72 md:h-72 rounded-full bg-white/65 border border-white shadow-sm"></div>
                        <?php 
                        $leadImg = $leadProd ? getFirstProductImage($leadProd['gambar']) : '';
                        if (!empty($leadImg)): 
                        ?>
                            <img src="<?= htmlspecialchars($leadImg) ?>" alt="<?= htmlspecialchars($leadProd['nama_induk']) ?>"
                                 class="relative z-10 h-40 sm:h-52 md:h-64 lg:h-72 object-contain mix-blend-multiply transition-transform duration-500 hover:scale-105">
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- SLIDE 2: ALAT KESEHATAN DIAGNOSTIK -->
            <div class="hero-slide w-full flex-col md:flex-row items-center justify-between gap-6 md:gap-8 px-2 sm:px-4 lg:px-6 py-8 sm:py-10 md:py-12" data-slide="1">
                <div class="flex-1 z-10 max-w-xl text-center md:text-left">
                    <p class="text-[10px] sm:text-xs font-bold uppercase tracking-[0.18em] text-[#0d9488] mb-2 sm:mb-3">
                        DIAGNOSTIK KLINIS &amp; PERAWATAN RUMAH
                    </p>
                    <h1 class="text-2xl sm:text-3xl lg:text-[42px] font-extrabold text-[#0f2d5a] leading-[1.2] tracking-tight mb-3 sm:mb-4">
                        Alat Kesehatan Akurat &amp; Bergaransi Resmi
                    </h1>
                    <p class="text-[#334155] text-xs sm:text-sm lg:text-base leading-relaxed mb-6 max-w-lg mx-auto md:mx-0">
                        Tensimeter digital, termometer klinis, pulse oximeter, hingga strip pengecekan darah dengan akurasi tinggi dan perlindungan garansi toko.
                    </p>
                    <div class="flex items-center justify-center md:justify-start gap-2.5 sm:gap-3 flex-wrap">
                        <button type="button" onclick="setCategoryFilter('Alat Kesehatan'); document.getElementById('katalog').scrollIntoView({behavior:'smooth'});" class="w-full sm:w-auto px-6 sm:px-7 py-3 sm:py-3.5 bg-[#0f2d5a] hover:bg-[#1a75d2] text-white font-bold text-xs uppercase tracking-widest transition cursor-pointer text-center">
                            JELAJAHI ALAT KESEHATAN
                        </button>
                        <a href="cara_pembelian.php" class="w-full sm:w-auto px-5 sm:px-6 py-3 sm:py-3.5 bg-white/85 hover:bg-white text-[#0f2d5a] border border-[#0f2d5a]/15 font-bold text-xs uppercase tracking-wider transition text-center">
                            PANDUAN BELANJA
                        </a>
                    </div>
                </div>
                <div class="shrink-0 flex items-center justify-center md:w-[360px] lg:w-[440px] z-10">
                    <div class="relative flex items-center justify-center w-full h-48 sm:h-64 md:h-80">
                        <div class="absolute w-44 h-44 sm:w-60 sm:h-60 md:w-72 md:h-72 rounded-full bg-white/65 border border-white shadow-sm"></div>
                        <?php 
                        $alkesImg = $alkesProd ? getFirstProductImage($alkesProd['gambar']) : '';
                        if (!empty($alkesImg)): 
                        ?>
                            <img src="<?= htmlspecialchars($alkesImg) ?>" alt="<?= htmlspecialchars($alkesProd['nama_induk']) ?>"
                                 class="relative z-10 h-40 sm:h-52 md:h-64 lg:h-72 object-contain mix-blend-multiply transition-transform duration-500 hover:scale-105">
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- SLIDE 3: OBAT KEMASAN BOX RESMI -->
            <div class="hero-slide w-full flex-col md:flex-row items-center justify-between gap-6 md:gap-8 px-2 sm:px-4 lg:px-6 py-8 sm:py-10 md:py-12" data-slide="2">
                <div class="flex-1 z-10 max-w-xl text-center md:text-left">
                    <p class="text-[10px] sm:text-xs font-bold uppercase tracking-[0.18em] text-[#1a75d2] mb-2 sm:mb-3">
                        JAMINAN MUTU &amp; KEDALUWARSA TERJAGA
                    </p>
                    <h1 class="text-2xl sm:text-3xl lg:text-[42px] font-extrabold text-[#0f2d5a] leading-[1.2] tracking-tight mb-3 sm:mb-4">
                        Sediaan Obat &amp; Vitamin Kemasan Utuh Pabrik
                    </h1>
                    <p class="text-[#334155] text-xs sm:text-sm lg:text-base leading-relaxed mb-6 max-w-lg mx-auto md:mx-0">
                        Setiap produk obat dikelola menggunakan sistem kontrol batch FEFO ketat. Tersedia pengiriman kurir nasional maupun layanan ambil langsung di toko.
                    </p>
                    <div class="flex items-center justify-center md:justify-start gap-2.5 sm:gap-3 flex-wrap">
                        <button type="button" onclick="setCategoryFilter('Obat'); document.getElementById('katalog').scrollIntoView({behavior:'smooth'});" class="w-full sm:w-auto px-6 sm:px-7 py-3 sm:py-3.5 bg-[#0f2d5a] hover:bg-[#1a75d2] text-white font-bold text-xs uppercase tracking-widest transition cursor-pointer text-center">
                            LIHAT KATALOG OBAT
                        </button>
                        <a href="tentang_kami.php" class="w-full sm:w-auto px-5 sm:px-6 py-3 sm:py-3.5 bg-white/85 hover:bg-white text-[#0f2d5a] border border-[#0f2d5a]/15 font-bold text-xs uppercase tracking-wider transition text-center">
                            TENTANG ZENCARE
                        </a>
                    </div>
                </div>
                <div class="shrink-0 flex items-center justify-center md:w-[360px] lg:w-[440px] z-10">
                    <div class="relative flex items-center justify-center w-full h-48 sm:h-64 md:h-80">
                        <div class="absolute w-44 h-44 sm:w-60 sm:h-60 md:w-72 md:h-72 rounded-full bg-white/65 border border-white shadow-sm"></div>
                        <?php 
                        $obatImg = $obatProd ? getFirstProductImage($obatProd['gambar']) : '';
                        if (!empty($obatImg)): 
                        ?>
                            <img src="<?= htmlspecialchars($obatImg) ?>" alt="<?= htmlspecialchars($obatProd['nama_induk']) ?>"
                                 class="relative z-10 h-40 sm:h-52 md:h-64 lg:h-72 object-contain mix-blend-multiply transition-transform duration-500 hover:scale-105">
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- Slide Navigation Dots -->
        <div class="absolute bottom-4 sm:bottom-5 left-0 right-0 flex items-center justify-center gap-2.5 z-20">
            <span class="slide-dot active" onclick="goSlide(0)"></span>
            <span class="slide-dot" onclick="goSlide(1)"></span>
            <span class="slide-dot" onclick="goSlide(2)"></span>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. PHARMIFY 5-CARD PROMOTIONAL BENTO GRID                    -->
    <!-- ============================================================ -->
    <section class="max-w-7xl mx-auto px-4 lg:px-8 pt-10 pb-6">
        <!-- Top Row: 3 Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            
            <!-- Card 1: Soft Ice Blue -->
            <div class="relative overflow-hidden bg-[#d7edf9] p-7 flex items-center justify-between min-h-[210px] group">
                <div class="z-10 max-w-[60%]">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#0f2d5a]/80 block mb-1.5">SEDIAAN KELUARGA</span>
                    <h3 class="text-xl font-extrabold text-[#0f2d5a] leading-tight mb-4">Sirup &amp; Suspensi Anak</h3>
                    <button type="button" onclick="quickFilterKeyword('Sirup')" class="px-5 py-2.5 bg-[#0f2d5a] hover:bg-[#1a75d2] text-white text-[11px] font-bold uppercase tracking-wider transition cursor-pointer">
                        BELANJA SEKARANG
                    </button>
                </div>
                <?php if ($imgSirup): ?>
                <div class="w-28 h-32 shrink-0 flex items-center justify-center">
                    <img src="<?= htmlspecialchars($imgSirup) ?>" alt="Sirup" class="max-h-full max-w-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-300">
                </div>
                <?php endif; ?>
            </div>

            <!-- Card 2: Deep Navy -->
            <div class="relative overflow-hidden bg-[#0c2454] p-7 flex items-center justify-between min-h-[210px] text-white group">
                <div class="z-10 max-w-[62%]">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-sky-300 block mb-1.5">DAYA TAHAN TUBUH</span>
                    <h3 class="text-xl font-extrabold text-white leading-tight mb-1">Vitamin &amp; Suplemen</h3>
                    <p class="text-xs text-sky-200 mb-4">Kemasan Box Grosir Resmi</p>
                    <button type="button" onclick="setCategoryFilter('Obat'); document.getElementById('katalog').scrollIntoView({behavior:'smooth'});" class="px-5 py-2.5 bg-[#86d5df] hover:bg-white text-[#0c2454] text-[11px] font-bold uppercase tracking-wider transition cursor-pointer">
                        LIHAT PRODUK
                    </button>
                </div>
                <?php if ($imgVitamin): ?>
                <div class="w-28 h-32 shrink-0 flex items-center justify-center bg-white/90 p-2 rounded">
                    <img src="<?= htmlspecialchars($imgVitamin) ?>" alt="Vitamin" class="max-h-full max-w-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-300">
                </div>
                <?php endif; ?>
            </div>

            <!-- Card 3: Clinical Aqua Teal -->
            <div class="relative overflow-hidden bg-[#82d3dc] p-7 flex items-center justify-between min-h-[210px] group">
                <div class="z-10 max-w-[60%]">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#0f2d5a]/80 block mb-1.5">AKURASI KLINIS</span>
                    <h3 class="text-xl font-extrabold text-[#0f2d5a] leading-tight mb-4">Alat Cek &amp; Diagnostik</h3>
                    <button type="button" onclick="setCategoryFilter('Alat Kesehatan'); document.getElementById('katalog').scrollIntoView({behavior:'smooth'});" class="px-5 py-2.5 bg-[#0f2d5a] hover:bg-[#1a75d2] text-white text-[11px] font-bold uppercase tracking-wider transition cursor-pointer">
                        BELANJA SEKARANG
                    </button>
                </div>
                <?php if ($imgDiagnostik): ?>
                <div class="w-28 h-32 shrink-0 flex items-center justify-center">
                    <img src="<?= htmlspecialchars($imgDiagnostik) ?>" alt="Diagnostik" class="max-h-full max-w-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-300">
                </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Bottom Row: 2 Wide Promotional Banners -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
            
            <!-- Wide Banner Left -->
            <div class="relative overflow-hidden bg-[#cbe6f8] px-5 sm:px-8 py-5 sm:py-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 min-h-[120px]">
                <svg class="absolute right-0 top-0 h-full opacity-35 pointer-events-none" viewBox="0 0 200 120" fill="none">
                    <polygon points="120,10 155,-10 190,10 190,50 155,70 120,50" stroke="#ffffff" stroke-width="2"/>
                    <polygon points="85,60 120,40 155,60 155,100 120,120 85,100" stroke="#ffffff" stroke-width="2"/>
                </svg>
                <div class="z-10">
                    <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-[0.15em] text-[#1a75d2] block mb-1">KEMASAN SATUAN BESAR</span>
                    <h4 class="text-base sm:text-xl font-extrabold text-[#0f2d5a] tracking-tight">HARGA GROSIR APOTEK &amp; KLINIK</h4>
                    <p class="text-xs text-[#334155] mt-0.5">Stok langsung terhubung dengan gudang utama ZenCare Medical</p>
                </div>
                <a href="#katalog" class="z-10 shrink-0 w-full sm:w-auto text-center px-5 py-2.5 bg-[#0f2d5a] hover:bg-[#1a75d2] text-white text-[11px] font-bold uppercase tracking-wider transition">
                    CEK KATALOG
                </a>
            </div>

            <!-- Wide Banner Right -->
            <div class="relative overflow-hidden bg-[#5d99ad] px-5 sm:px-8 py-5 sm:py-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 min-h-[120px] text-white">
                <!-- ECG Line Decoration -->
                <svg class="absolute right-6 top-1/2 -translate-y-1/2 w-48 h-16 opacity-25 pointer-events-none" viewBox="0 0 200 60" fill="none" stroke="#ffffff" stroke-width="2.5">
                    <polyline points="0,30 45,30 58,10 70,52 84,6 98,45 108,30 200,30" />
                </svg>
                <div class="z-10">
                    <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-[0.15em] text-sky-100 block mb-1">OPSI PENGIRIMAN FLEKSIBEL</span>
                    <h4 class="text-base sm:text-xl font-extrabold text-white tracking-tight">LAYANAN AMBIL DI TOKO BEBAS ONGKIR</h4>
                    <p class="text-xs text-sky-100 mt-0.5">Pesan online dari rumah, ambil langsung tanpa antre di cabang Muharto</p>
                </div>
                <a href="cara_pembelian.php" class="z-10 shrink-0 w-full sm:w-auto text-center px-5 py-2.5 bg-white hover:bg-sky-50 text-[#0f2d5a] text-[11px] font-bold uppercase tracking-wider transition">
                    CARA PESAN
                </a>
            </div>

        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. SHOP OUR CATEGORIES (6-Column Pharmify Showcase)          -->
    <!-- ============================================================ -->
    <section class="max-w-7xl mx-auto px-4 lg:px-8 py-8 sm:py-10">
        <div class="text-center mb-6 sm:mb-8">
            <h2 class="text-lg sm:text-2xl font-extrabold text-[#0f2d5a] uppercase tracking-wider">JELAJAHI KATEGORI PRODUK</h2>
        </div>

        <div class="grid grid-cols-3 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-4 lg:gap-5">
            <?php
            $showcaseCats = [
                ['label' => 'Obat Tablet & Kaplet', 'img' => $imgTablet,     'action' => "setCategoryFilter('Obat')"],
                ['label' => 'Sirup & Suspensi',     'img' => $imgSirup,      'action' => "quickFilterKeyword('Sirup')"],
                ['label' => 'Vitamin & Suplemen',   'img' => $imgVitamin,    'action' => "quickFilterKeyword('Vitamin')"],
                ['label' => 'Alat Cek Diagnostik',  'img' => $imgDiagnostik, 'action' => "setCategoryFilter('Alat Kesehatan')"],
                ['label' => 'BMHP & Antiseptik',    'img' => $imgBmhp,       'action' => "quickFilterKeyword('Alkohol')"],
                ['label' => 'Alat Bantu Medis',     'img' => $imgTerapi,     'action' => "setCategoryFilter('Alat Kesehatan')"],
            ];
            foreach ($showcaseCats as $sc):
            ?>
            <button type="button" onclick="<?= $sc['action'] ?>; document.getElementById('katalog').scrollIntoView({behavior:'smooth'});" class="group text-center focus:outline-none cursor-pointer">
                <div class="aspect-square w-full bg-[#eef6fb] group-hover:bg-[#dfeef9] transition-colors flex items-center justify-center p-3 sm:p-5 mb-2 sm:mb-3">
                    <?php if (!empty($sc['img'])): ?>
                        <img src="<?= htmlspecialchars($sc['img']) ?>" alt="<?= htmlspecialchars($sc['label']) ?>" class="max-h-full max-w-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-300">
                    <?php else: ?>
                        <svg class="w-8 h-8 sm:w-12 sm:h-12 text-[#1a75d2]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <?php endif; ?>
                </div>
                <span class="text-[11px] sm:text-xs font-bold text-[#0f2d5a] group-hover:text-[#1a75d2] transition-colors block leading-tight">
                    <?= htmlspecialchars($sc['label']) ?>
                </span>
            </button>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. KOLEKSI PRODUK KESEHATAN (Katalog Utama & Filter)         -->
    <!-- ============================================================ -->
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-6 sm:py-8 pb-16">
        
        <section id="katalog" class="scroll-mt-20">
            
            <!-- Centered Heading & Underline Category Tabs (Pharmify Reference 8) -->
            <div class="text-center mb-6 sm:mb-8">
                <h2 class="text-xl sm:text-3xl font-extrabold text-[#0f2d5a] tracking-tight mb-4 sm:mb-5">Koleksi Produk Kesehatan</h2>
                <div class="inline-flex items-center justify-start sm:justify-center gap-4 sm:gap-8 border-b border-slate-200 px-3 sm:px-4 text-xs sm:text-sm overflow-x-auto no-scrollbar max-w-full whitespace-nowrap">
                    <button type="button" onclick="setCategoryFilter('all')" id="cat_pill_all" class="cat-filter-btn cursor-pointer active">
                        Semua Produk (<?= count($products) ?>)
                    </button>
                    <button type="button" onclick="setCategoryFilter('Obat')" id="cat_pill_Obat" class="cat-filter-btn cursor-pointer">
                        Obat-obatan (<?= $catCounts['Obat'] ?? 0 ?>)
                    </button>
                    <button type="button" onclick="setCategoryFilter('Alat Kesehatan')" id="cat_pill_Alat_Kesehatan" class="cat-filter-btn cursor-pointer">
                        Alat Kesehatan (<?= $catCounts['Alat Kesehatan'] ?? 0 ?>)
                    </button>
                </div>
            </div>

            <!-- Minimalist Filter & Toolbar Row -->
            <div class="bg-[#f8fafc] border border-slate-200/80 px-4 py-3.5 mb-8 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 text-xs">
                
                <!-- Left: Filter Produk Induk + Search -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
                    <div class="relative flex-1 max-w-xs">
                        <input type="text" id="catalog_search" value="<?= htmlspecialchars($initQuery) ?>" oninput="onSearchChange(this.value)" placeholder="Cari nama produk, variasi, atau SKU..." class="w-full text-xs pl-8 pr-3 py-2 border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-[#0f2d5a]">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <div class="flex items-center gap-2">
                        <label for="filter_induk" class="font-semibold text-slate-600 shrink-0">Produk Induk:</label>
                        <select id="filter_induk" onchange="onParentFilterChange(this.value)" class="text-xs border border-slate-200 bg-white px-3 py-2 font-medium text-slate-700 focus:outline-none focus:border-[#0f2d5a] w-full sm:w-56">
                            <option value="all">Semua Produk Induk</option>
                            <?php foreach ($parentProducts as $pi): ?>
                            <option value="<?= $pi['id'] ?>" data-kategori="<?= htmlspecialchars($pi['kategori']) ?>" <?= ($initInduk == $pi['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pi['nama']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="button" id="reset_filter_btn" onclick="resetFilters()" class="hidden px-3 py-2 text-xs text-rose-700 bg-rose-50 hover:bg-rose-100 font-semibold border border-rose-200 transition cursor-pointer">
                        Reset Filter
                    </button>
                </div>

                <!-- Right: Count + Sort + Per Page -->
                <div class="flex items-center justify-between sm:justify-end gap-4 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-200/70">
                    <span id="filter_result_count" class="text-slate-500">
                        Menampilkan <strong class="text-[#0f2d5a]" id="count_display"><?= count($products) ?></strong> produk
                    </span>

                    <div class="flex items-center gap-1.5">
                        <label for="sort_select" class="text-slate-500">Urutkan:</label>
                        <select id="sort_select" onchange="sortAndFilterProducts()" class="text-xs border border-slate-200 bg-white px-2.5 py-1.5 font-semibold text-[#0f2d5a] focus:outline-none focus:border-[#0f2d5a]">
                            <option value="default">Standar</option>
                            <option value="price_asc">Harga Terendah</option>
                            <option value="price_desc">Harga Tertinggi</option>
                            <option value="name_asc">Nama (A–Z)</option>
                            <option value="stok_desc">Stok Terbanyak</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <label for="per_page_select" class="text-slate-500 hidden sm:inline">Tampil:</label>
                        <select id="per_page_select" onchange="changePerPage(this.value)" class="text-xs border border-slate-200 bg-white px-2.5 py-1.5 font-semibold text-[#0f2d5a] focus:outline-none focus:border-[#0f2d5a]">
                            <option value="8">8</option>
                            <option value="12" selected>12</option>
                            <option value="16">16</option>
                            <option value="20">20</option>
                            <option value="all">Semua</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Product Grid (Pharmify Minimalist Cards - Reference 9) -->
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6" id="product-grid">
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
                ?>
                <div class="prod-card group flex flex-col bg-white"
                     data-id="<?= $p['id'] ?>"
                     data-induk-id="<?= $p['id_produk_induk'] ?>"
                     data-kategori="<?= htmlspecialchars($p['kategori']) ?>"
                     data-name="<?= strtolower(htmlspecialchars($p['nama_produk'])) ?>"
                     data-sku="<?= strtolower(htmlspecialchars($p['sku_variasi'])) ?>"
                     data-price="<?= $harga ?>"
                     data-stok="<?= $stokBox ?>">

                    <!-- Square Minimalist Image Container (#f6f8fa) -->
                    <?php 
                        $firstImg = getFirstProductImage($p['gambar'] ?? '');
                        $allImgs = array_filter(array_map('trim', explode(',', $p['gambar'] ?? '')));
                        $totalImgs = count($allImgs);
                    ?>
                    <a href="produk.php?id=<?= $p['id'] ?>" class="card-img-box relative block aspect-square w-full overflow-hidden mb-2 sm:mb-3.5">
                        <?php if (!empty($firstImg)): ?>
                            <img src="<?= htmlspecialchars($firstImg) ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>"
                                 class="w-full h-full object-contain p-3 sm:p-6 mix-blend-multiply"
                                 onerror="this.onerror=null; this.src='../assets/img/no-image.png';">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                <svg class="w-10 h-10 sm:w-14 sm:h-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                            </div>
                        <?php endif; ?>

                        <!-- Subtle Top-Left Stock Status Pill -->
                        <span class="absolute top-2 left-2 sm:top-3 sm:left-3 px-1.5 sm:px-2.5 py-0.5 text-[9px] sm:text-[10px] font-semibold tracking-wide <?= $stokBadge ?>">
                            <?= $stokLbl ?>
                        </span>

                        <?php if ($totalImgs > 1): ?>
                            <span class="absolute bottom-2 right-2 sm:bottom-2.5 sm:right-2.5 px-1.5 sm:px-2 py-0.5 bg-white/90 text-slate-600 text-[9px] sm:text-[10px] font-medium border border-slate-200/70">
                                <?= $totalImgs ?> Foto
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- Minimalist Card Info -->
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

                        <!-- Price Row -->
                        <div class="flex flex-wrap items-baseline gap-1 sm:gap-2 mb-2 sm:mb-3.5 mt-auto pt-1">
                            <span class="text-sm sm:text-base font-extrabold text-[#0f2d5a]">Rp <?= number_format($harga, 0, ',', '.') ?></span>
                            <span class="text-[10px] sm:text-xs text-slate-400 font-medium">/ <?= $satB ?></span>
                            <?php if ($p['kategori'] === 'Obat' && $hargaEcr > 0 && $rasio > 1): ?>
                                <span class="hidden sm:inline text-[11px] text-slate-400 ml-auto">~Rp <?= number_format($hargaEcr, 0, ',', '.') ?>/<?= $satK ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Action Controls (Pharmify Pill CTA + Compact Qty Stepper) -->
                        <?php if ($stokBox >= 1): ?>
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <!-- Compact Stepper -->
                            <div class="flex items-center border border-slate-200 bg-[#f8fafc] h-8 sm:h-9 shrink-0">
                                <button type="button" onclick="adjustQty(<?= $p['id'] ?>, -1, <?= $stokBox ?>)"
                                    class="w-5 sm:w-7 h-full flex items-center justify-center hover:bg-slate-200/60 text-slate-700 font-bold transition text-xs sm:text-sm cursor-pointer">−</button>
                                <input type="number" id="qty_<?= $p['id'] ?>" value="1" min="1" max="<?= $stokBox ?>" readonly
                                    class="w-6 sm:w-8 text-center text-[11px] sm:text-xs font-bold bg-transparent text-[#0f2d5a] focus:outline-none select-none">
                                <button type="button" onclick="adjustQty(<?= $p['id'] ?>, 1, <?= $stokBox ?>)"
                                    class="w-5 sm:w-7 h-full flex items-center justify-center hover:bg-slate-200/60 text-slate-700 font-bold transition text-xs sm:text-sm cursor-pointer">+</button>
                            </div>

                            <!-- Solid Navy Pill Add To Cart Button -->
                            <button type="button" onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars_decode($p['nama_produk'])) ?>', <?= $harga ?>, <?= intval($p['berat_gram']) ?>, '<?= addslashes($p['gambar'] ?? '') ?>', '<?= addslashes($satB) ?>', <?= $rasio ?>, <?= $stokBox ?>, document.getElementById('qty_<?= $p['id'] ?>').value)"
                                class="flex-1 h-8 sm:h-9 px-2 sm:px-3 rounded-full bg-[#0f2d5a] hover:bg-[#1a75d2] text-white text-[10px] sm:text-[11px] font-bold uppercase tracking-wider transition cursor-pointer flex items-center justify-center gap-1">
                                <span>+ Keranjang</span>
                            </button>

                            <!-- Quick Buy Now Icon/Button (Desktop & Tablet) -->
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
            </div>

            <!-- Empty Search / Filter State -->
            <div id="empty_search_state" class="hidden py-16 text-center border border-slate-200/80 bg-[#f8fafc] my-4">
                <div class="w-12 h-12 mx-auto mb-3 flex items-center justify-center text-slate-400">
                    <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                </div>
                <h4 class="text-base font-bold text-[#0f2d5a] mb-1">Tidak ada produk yang ditemukan</h4>
                <p class="text-xs text-slate-500 mb-5">Coba ubah kata kunci pencarian atau reset filter kategori produk.</p>
                <button type="button" onclick="resetFilters()" class="px-6 py-2.5 bg-[#0f2d5a] text-white text-xs font-bold uppercase tracking-wider hover:bg-[#1a75d2] transition cursor-pointer">
                    Tampilkan Semua Produk
                </button>
            </div>

            <!-- Pagination Controls -->
            <div id="pagination_container" class="mt-12 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
                <span id="page_info" class="text-slate-500 font-medium">
                    Menampilkan 1 – 12 dari <?= count($products) ?> produk
                </span>
                <div id="pagination_buttons" class="flex items-center gap-1.5 flex-wrap">
                    <!-- Populated dynamically by JS -->
                </div>
            </div>

        </section>

    </div>

    <!-- ============================================================ -->
    <!-- 5. MINIMALIST CLINICAL SERVICE STRIP                         -->
    <!-- ============================================================ -->
    <section class="border-t border-slate-200 bg-[#f6f9fc]">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 py-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="flex items-start gap-3.5">
                    <svg class="w-6 h-6 text-[#0f2d5a] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <div>
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-[#0f2d5a]">100% Produk Resmi</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Jaminan segel pabrik &amp; kontrol batch FEFO</p>
                    </div>
                </div>
                <div class="flex items-start gap-3.5">
                    <svg class="w-6 h-6 text-[#0f2d5a] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                    <div>
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-[#0f2d5a]">Pengiriman Nasional</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Dukungan kurir JNE, POS, dan TIKI</p>
                    </div>
                </div>
                <div class="flex items-start gap-3.5">
                    <svg class="w-6 h-6 text-[#0f2d5a] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <div>
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-[#0f2d5a]">Ambil di Toko (Pick-Up)</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Bebas biaya ongkos kirim &amp; tanpa antre</p>
                    </div>
                </div>
                <div class="flex items-start gap-3.5">
                    <svg class="w-6 h-6 text-[#0f2d5a] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <div>
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-[#0f2d5a]">Transaksi Terproteksi</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Pembayaran otomatis via Midtrans &amp; Bank</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<script>
// ── Slideshow Controller ───────────────────────────────────────────
let curSlide = 0;
const slides = document.querySelectorAll('.hero-slide');
const dots   = document.querySelectorAll('.slide-dot');
let slideTimer = null;

function goSlide(idx) {
    if (!slides.length) return;
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
    if (slides.length > 1) slideTimer = setInterval(() => goSlide(curSlide + 1), 5500);
}
if (slides.length > 1) restartTimer();

// ── Product Catalog Controller (Filter Induk, Limit & Pagination) ──
let currentCategory = '<?= $initCategory !== "all" ? addslashes($initCategory) : "all" ?>';
let currentParentId = '<?= $initInduk !== "all" ? addslashes($initInduk) : "all" ?>';
let currentSearch   = '<?= addslashes(strtolower($initQuery)) ?>';
let currentSort     = 'default';
let currentPage     = 1;
let perPage         = 12;

function quickFilterKeyword(kw) {
    setCategoryFilter('all');
    const searchInput = document.getElementById('catalog_search');
    if (searchInput) {
        searchInput.value = kw;
    }
    currentSearch = kw.trim().toLowerCase();
    currentPage = 1;
    renderCatalog();
}

function setCategoryFilter(cat) {
    currentCategory = cat;
    currentPage = 1;

    // Update active tab styling
    document.querySelectorAll('.cat-filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    let activeBtnId = (cat === 'all') ? 'cat_pill_all' : (cat === 'Obat' ? 'cat_pill_Obat' : 'cat_pill_Alat_Kesehatan');
    let activeBtn = document.getElementById(activeBtnId);
    if (activeBtn) {
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

function sortAndFilterProducts() {
    onSortChange();
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
        grid.appendChild(filtered[i]);
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
            btnsHtml += `<button type="button" onclick="goToPage(${currentPage - 1})" class="px-3.5 py-1.5 border border-slate-200 bg-white hover:bg-slate-50 font-semibold text-[#0f2d5a] transition cursor-pointer">&larr; Sebelumnya</button>`;
        } else {
            btnsHtml += `<span class="px-3.5 py-1.5 border border-slate-200 bg-slate-50 text-slate-300 font-semibold cursor-not-allowed">&larr; Sebelumnya</span>`;
        }

        // Nomor Halaman
        for (let p = 1; p <= totalPages; p++) {
            if (p === 1 || p === totalPages || (p >= currentPage - 1 && p <= currentPage + 1)) {
                let activeCls = (p === currentPage) 
                    ? 'bg-[#0f2d5a] border-[#0f2d5a] text-white' 
                    : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50';
                btnsHtml += `<button type="button" onclick="goToPage(${p})" class="w-8 h-8 border text-xs font-bold transition cursor-pointer ${activeCls}">${p}</button>`;
            } else if (p === 2 || p === totalPages - 1) {
                btnsHtml += `<span class="px-1 text-slate-400">&hellip;</span>`;
            }
        }

        // Tombol Selanjutnya
        if (currentPage < totalPages) {
            btnsHtml += `<button type="button" onclick="goToPage(${currentPage + 1})" class="px-3.5 py-1.5 border border-slate-200 bg-white hover:bg-slate-50 font-semibold text-[#0f2d5a] transition cursor-pointer">Selanjutnya &rarr;</button>`;
        } else {
            btnsHtml += `<span class="px-3.5 py-1.5 border border-slate-200 bg-slate-50 text-slate-300 font-semibold cursor-not-allowed">Selanjutnya &rarr;</span>`;
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
    <?php if ($initCategory !== 'all' || $initInduk !== 'all' || $initQuery !== ''): ?>
    setTimeout(() => {
        const el = document.getElementById('katalog');
        if (el) el.scrollIntoView({ behavior: 'smooth' });
    }, 150);
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
