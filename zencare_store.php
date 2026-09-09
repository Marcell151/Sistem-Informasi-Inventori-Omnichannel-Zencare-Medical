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

                <?php if (isset($_SESSION['user_id']) && in_array($_SESSION['role'] ?? '', ['super_admin','karyawan'])): ?>
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

                <!-- Cart Button (Side Drawer Toggle) -->
                <button onclick="toggleCartDrawer()" class="relative inline-flex items-center gap-2 px-4 py-2 bg-zc hover:bg-zcHv text-white text-xs font-bold rounded-xl transition shadow-sm">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.55L23 6H6"/></svg>
                    <span class="hidden sm:inline">Keranjang</span>
                    <span id="cart-badge" class="px-1.5 py-0.5 text-[10px] font-bold bg-white text-zc rounded-full min-w-[18px] text-center">0</span>
                </button>
            </div>
        </div>
        <!-- Mega Menu / Nav -->
        <div class="border-t border-zcBrd bg-white hidden md:block">
            <div class="max-w-7xl mx-auto px-4 lg:px-8 h-10 flex items-center gap-6 text-[11px] font-bold text-zcMut uppercase tracking-widest">
                <a href="zencare_store.php" class="text-zc hover:text-zcHv">Beranda</a>
                <div class="group relative py-3">
                    <a href="#" class="hover:text-zcTxt flex items-center gap-1">
                        Kategori Produk
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                    </a>
                    <!-- Dropdown -->
                    <div class="absolute top-full left-0 w-48 bg-white border border-zcBrd rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 py-2">
                        <button onclick="filterCat('semua')" class="w-full text-left px-4 py-2 hover:bg-slate-50 hover:text-zc transition">Semua Produk</button>
                        <?php foreach ($categories as $cat): ?>
                        <button onclick="filterCat('<?= htmlspecialchars(addslashes($cat)) ?>')" class="w-full text-left px-4 py-2 hover:bg-slate-50 hover:text-zc transition"><?= htmlspecialchars($cat) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <a href="#" class="hover:text-zcTxt">Alat Kesehatan</a>
                <a href="#" class="hover:text-zcTxt">Obat &amp; Suplemen</a>
                <a href="#" class="hover:text-zcTxt">Promo B2B</a>
            </div>
        </div>
    </header>

    <!-- Minimal Whitespace Banner (Pharmify Style) -->
    <section class="max-w-7xl mx-auto px-4 lg:px-8 pt-8 pb-4">
        <div class="bg-gradient-to-r from-slate-50 to-white border border-zcBrd rounded-2xl p-8 sm:p-12 flex flex-col md:flex-row items-center justify-between gap-8 shadow-sm">
            <div class="max-w-lg">
                <span class="inline-block px-3 py-1 bg-zc/10 text-zc text-[10px] font-bold uppercase tracking-widest rounded-md mb-4 border border-zc/20">Distributor Resmi</span>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-zcTxt tracking-tight leading-[1.15] mb-4">Solusi Pengadaan<br>Alkes &amp; Obat Medis.</h1>
                <p class="text-zcMut text-sm leading-relaxed mb-6">Pembelian grosir B2B untuk klinik dan apotek dengan jaminan produk bersertifikat BPOM & Kemenkes RI.</p>
                <div class="flex gap-3">
                    <button class="px-5 py-2.5 bg-zc text-white text-xs font-bold rounded-xl hover:bg-zcHv transition">Belanja Sekarang</button>
                    <button class="px-5 py-2.5 bg-white text-zcTxt text-xs font-bold rounded-xl border border-zcBrd hover:border-zc transition">Lihat Katalog</button>
                </div>
            </div>
            <div class="hidden md:flex items-center justify-center w-64 h-48 bg-slate-50 rounded-2xl border border-zcBrd/50 relative overflow-hidden">
                <!-- Abstract Clean Illustration Placeholder -->
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNlNGU5ZjAiLz48L3N2Zz4=')] opacity-50"></div>
                <svg class="w-24 h-24 text-zc/20 relative z-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </div>
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
                $stokPcs  = intval($p['stok_sistem']);
                $rasio    = intval($p['rasio_konversi']) ?: 1;
                $stokBox  = floor($stokPcs / $rasio);
                $harga    = floatval($p['harga_jual']);
                $hargaEceran = floatval($p['harga_eceran']);
                $satBesar = $p['satuan_besar'];
                $satKecil = $p['satuan_kecil'];
                // Stock status
                if ($stokBox <= 0) { $stokClass = 'bg-rose-50 text-rose-600 border-rose-200'; $stokDot = 'bg-rose-500'; $stokLabel = 'Habis'; }
                elseif ($stokBox <= 5) { $stokClass = 'bg-amber-50 text-amber-700 border-amber-200'; $stokDot = 'bg-amber-400'; $stokLabel = "Sisa $stokBox $satBesar"; }
                else { $stokClass = 'bg-emerald-50 text-emerald-700 border-emerald-200'; $stokDot = 'bg-emerald-500'; $stokLabel = "$stokBox $satBesar Tersedia"; }
                // Description excerpt — first sentence only for compactness
                $desc = trim($p['deskripsi'] ?? '');
                $descShort = $desc ? mb_strimwidth($desc, 0, 72, '…') : 'Produk medis berkualitas & bersertifikat resmi.';
                // SKU badge label
                $sku = $p['sku_variasi'] ?? '';
            ?>
            <!-- ── PRODUCT CARD ── -->
            <div class="product-card group bg-white rounded-2xl overflow-hidden flex flex-col"
                 style="box-shadow:0 1px 4px rgba(0,0,0,.07);"
                 data-cat="<?= htmlspecialchars($p['kategori']) ?>"
                 data-name="<?= strtolower(htmlspecialchars($p['nama_produk'])) ?>"
                 data-price="<?= $harga ?>"
                 data-id="<?= $p['id'] ?>">

                <!-- ── IMAGE ZONE ── -->
                <a href="ecommerce/produk.php?id=<?= $p['id'] ?>" class="relative bg-gradient-to-br from-slate-50 via-blue-50/30 to-slate-100 overflow-hidden block" style="height:176px">
                    <?php if (!empty($p['gambar'])): ?>
                        <img src="<?= htmlspecialchars($p['gambar']) ?>"
                             alt="<?= htmlspecialchars($p['nama_produk']) ?>"
                             class="w-full h-full object-contain p-3 transition-transform duration-300 group-hover:scale-105">
                    <?php else: ?>
                        <div class="w-full h-full flex flex-col items-center justify-center gap-2">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-zcLt to-blue-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-zc/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                            <span class="text-[10px] font-medium text-slate-400">Belum Ada Foto</span>
                        </div>
                    <?php endif; ?>

                    <!-- Category pill – top left -->
                    <div class="absolute top-2.5 left-2.5">
                        <span class="px-2 py-0.5 bg-white/90 backdrop-blur-sm text-zc text-[9px] font-bold rounded-md border border-zc/15 uppercase tracking-wider">
                            <?= htmlspecialchars($p['kategori']) ?>
                        </span>
                    </div>

                    <!-- Stock indicator – top right -->
                    <div class="absolute top-2.5 right-2.5">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md border text-[9px] font-bold <?= $stokClass ?> backdrop-blur-sm bg-white/80">
                            <span class="w-1.5 h-1.5 rounded-full <?= $stokDot ?> shrink-0"></span>
                            <?= htmlspecialchars($stokLabel) ?>
                        </span>
                    </div>

                    <!-- SKU micro-badge – bottom left -->
                    <?php if ($sku): ?>
                    <div class="absolute bottom-2 left-2.5">
                        <span class="px-1.5 py-0.5 bg-slate-900/60 text-white text-[9px] font-mono rounded tracking-wide">
                            <?= htmlspecialchars($sku) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </a>

                <!-- ── CONTENT BODY ── -->
                <div class="p-4 flex flex-col flex-1 gap-0">

                    <!-- Product name -->
                    <a href="ecommerce/produk.php?id=<?= $p['id'] ?>" class="block group-hover:text-zc transition">
                        <h3 class="text-[13px] font-bold text-zcTxt leading-snug line-clamp-2 mb-2.5">
                            <?= htmlspecialchars($p['nama_induk']) ?>
                            <span class="font-normal text-zcMut"> &mdash; <?= htmlspecialchars($p['nama_variasi']) ?></span>
                        </h3>
                    </a>

                    <!-- ── MINI SPECS SHEET ── -->
                    <div class="mb-3 rounded-lg border border-slate-100 bg-slate-50/80 divide-y divide-slate-100">
                        <!-- Kategori -->
                        <div class="flex items-center gap-2 px-2.5 py-1.5">
                            <svg class="w-3 h-3 text-zc/60 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            <span class="text-[10px] text-slate-400 w-16 shrink-0">Kategori</span>
                            <span class="text-[10px] font-semibold text-zcTxt truncate"><?= htmlspecialchars($p['kategori']) ?></span>
                        </div>
                        <!-- Kandungan / Deskripsi singkat -->
                        <div class="flex items-start gap-2 px-2.5 py-1.5">
                            <svg class="w-3 h-3 text-zc/60 shrink-0 mt-px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                            <span class="text-[10px] text-slate-400 w-16 shrink-0">Keterangan</span>
                            <span class="text-[10px] font-medium text-zcMut leading-relaxed line-clamp-2"><?= htmlspecialchars($descShort) ?></span>
                        </div>
                        <!-- Konversi / Kemasan -->
                        <div class="flex items-center gap-2 px-2.5 py-1.5">
                            <svg class="w-3 h-3 text-zc/60 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                            <span class="text-[10px] text-slate-400 w-16 shrink-0">Kemasan</span>
                            <span class="text-[10px] font-semibold text-zcTxt">
                                1&nbsp;<?= htmlspecialchars($satBesar) ?> &nbsp;=&nbsp; <?= $rasio ?>&nbsp;<?= htmlspecialchars($satKecil) ?>
                            </span>
                        </div>
                    </div>

                    <!-- ── PRICE BLOCK ── -->
                    <div class="mb-3">
                        <!-- Grosir price (primary) -->
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-[17px] font-extrabold text-zcTxt tracking-tight">
                                Rp <?= number_format($harga, 0, ',', '.') ?>
                            </span>
                            <span class="text-[11px] text-zcMut font-medium">/ <?= htmlspecialchars($satBesar) ?></span>
                        </div>
                        <?php if ($hargaEceran > 0): ?>
                        <!-- Retail / eceran reference (secondary) -->
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="text-[10px] text-zcMut">
                                ~Rp <?= number_format($hargaEceran, 0, ',', '.') ?>
                                <span class="text-slate-400">/ <?= htmlspecialchars($satKecil) ?> (ref. eceran)</span>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- ── CTA & QTY ── -->
                    <div class="mt-auto">
                    <?php if ($stokBox >= 1): ?>
                        <!-- Qty stepper -->
                        <div class="flex items-center gap-2 mb-2.5">
                            <div class="flex items-center bg-slate-50 border border-slate-200 rounded-lg overflow-hidden flex-1">
                                <button type="button" onclick="adjustQty(<?= $p['id'] ?>, -1, <?= $stokBox ?>)"
                                    class="w-8 h-8 flex items-center justify-center hover:bg-slate-100 text-zcTxt font-bold text-sm transition border-r border-slate-200">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M5 12h14"/></svg>
                                </button>
                                <input type="number" id="qty_<?= $p['id'] ?>" value="1" min="1" max="<?= $stokBox ?>" readonly
                                    class="flex-1 text-center text-xs font-bold py-1 bg-transparent focus:outline-none select-none">
                                <button type="button" onclick="adjustQty(<?= $p['id'] ?>, 1, <?= $stokBox ?>)"
                                    class="w-8 h-8 flex items-center justify-center hover:bg-slate-100 text-zcTxt font-bold text-sm transition border-l border-slate-200">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                                </button>
                            </div>
                            <span class="text-[9px] text-slate-400 shrink-0 leading-tight text-right">Max<br><?= $stokBox ?> <?= htmlspecialchars($satBesar) ?></span>
                        </div>
                        <!-- Add to cart button -->
                        <button onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes($p['nama_produk']) ?>', <?= $harga ?>, <?= intval($p['berat_gram']) ?>, '<?= addslashes($p['gambar'] ?? '') ?>', '<?= addslashes($satBesar) ?>', <?= $rasio ?>, <?= $stokBox ?>)"
                            class="w-full flex items-center justify-center gap-2 text-xs font-bold py-2.5 px-3 rounded-xl transition
                                   bg-zc hover:bg-zcHv active:scale-[.98] text-white shadow-sm">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.55L23 6H6"/>
                            </svg>
                            Beli
                        </button>
                    <?php else: ?>
                        <a href="https://wa.me/6281234567890?text=Halo%20Admin,%20saya%20ingin%20menanyakan%20stok%20untuk%20produk%20<?= urlencode($p['nama_induk']) ?>" target="_blank" class="w-full flex items-center justify-center gap-2 text-xs font-bold py-2.5 px-3 rounded-xl transition bg-[#25D366] hover:bg-[#1DA851] active:scale-[.98] text-white shadow-sm">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
                            Hubungi via WhatsApp
                        </a>
                    <?php endif; ?>
                    </div><!-- /mt-auto -->

                </div><!-- /content body -->
            </div><!-- /product-card -->
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
    <!-- SIDE DRAWER CART                                              -->
    <!-- =========================================================== -->
    <div id="cart-drawer-overlay" class="fixed inset-0 bg-slate-900/40 z-[60] opacity-0 invisible transition-all duration-300 backdrop-blur-sm" onclick="toggleCartDrawer()"></div>
    <div id="cart-drawer" class="fixed top-0 right-0 h-full w-full sm:w-[400px] bg-white z-[70] shadow-2xl transform translate-x-full transition-transform duration-300 flex flex-col border-l border-zcBrd">
        <div class="px-5 py-4 border-b border-zcBrd flex items-center justify-between bg-slate-50">
            <h2 class="text-sm font-bold text-zcTxt flex items-center gap-2">
                <svg class="w-4 h-4 text-zc" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.55L23 6H6"/></svg>
                Keranjang Belanja
            </h2>
            <button onclick="toggleCartDrawer()" class="text-zcMut hover:text-rose-500 transition p-1">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="cart-drawer-items" class="flex-1 overflow-y-auto p-5 flex flex-col gap-4">
            <!-- Cart items injected here -->
        </div>
        <div class="p-5 border-t border-zcBrd bg-slate-50">
            <div class="flex justify-between items-center mb-4">
                <span class="text-xs font-semibold text-zcMut">Subtotal:</span>
                <span id="cart-drawer-total" class="text-lg font-extrabold text-zcTxt tracking-tight">Rp 0</span>
            </div>
            <a href="zencare_checkout.php" class="flex items-center justify-center w-full py-3 bg-zc hover:bg-zcHv text-white text-xs font-bold rounded-xl transition shadow-md">
                Lanjut ke Pembayaran
            </a>
            <button onclick="toggleCartDrawer()" class="w-full text-center py-2 mt-2 text-xs font-medium text-zcMut hover:text-zcTxt transition">
                Lanjutkan Belanja
            </button>
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

    // ─── Cart Drawer Logic ────────────────────────────────────────
    function toggleCartDrawer() {
        const drawer = document.getElementById('cart-drawer');
        const overlay = document.getElementById('cart-drawer-overlay');
        const isOpen = !drawer.classList.contains('translate-x-full');
        
        if (isOpen) {
            drawer.classList.add('translate-x-full');
            overlay.classList.remove('opacity-100', 'visible');
            overlay.classList.add('opacity-0', 'invisible');
        } else {
            renderCartDrawer();
            drawer.classList.remove('translate-x-full');
            overlay.classList.remove('opacity-0', 'invisible');
            overlay.classList.add('opacity-100', 'visible');
        }
    }

    function renderCartDrawer() {
        const cart = getCart();
        const container = document.getElementById('cart-drawer-items');
        const totalEl = document.getElementById('cart-drawer-total');
        
        if (cart.length === 0) {
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center h-full text-center gap-3 opacity-60">
                    <svg class="w-12 h-12 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.55L23 6H6"/></svg>
                    <p class="text-xs font-medium text-zcMut">Keranjang masih kosong</p>
                </div>
            `;
            totalEl.innerText = 'Rp 0';
            return;
        }

        let html = '';
        let total = 0;

        cart.forEach(item => {
            const sub = item.price * item.qty;
            total += sub;
            html += `
            <div class="flex gap-3 bg-white border border-zcBrd rounded-xl p-2">
                <div class="w-16 h-16 rounded-lg bg-slate-50 flex items-center justify-center shrink-0 border border-slate-100 overflow-hidden">
                    ${item.image ? `<img src="${item.image}" class="w-full h-full object-contain p-1">` : `<svg class="w-6 h-6 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>`}
                </div>
                <div class="flex-1 flex flex-col justify-between py-0.5">
                    <div class="flex justify-between items-start gap-2">
                        <p class="text-[11px] font-bold text-zcTxt leading-tight line-clamp-2">${item.name}</p>
                        <button onclick="removeDrawerItem(${item.id})" class="text-rose-400 hover:text-rose-600 transition shrink-0">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                        </button>
                    </div>
                    <div class="flex justify-between items-end">
                        <span class="text-[11px] font-semibold text-zc">Rp ${new Intl.NumberFormat('id-ID').format(item.price)}</span>
                        <div class="flex items-center gap-2 text-[10px]">
                            <button onclick="updateDrawerQty(${item.id}, -1)" class="w-5 h-5 flex items-center justify-center bg-slate-100 rounded text-slate-600 hover:bg-slate-200">-</button>
                            <span class="font-bold w-4 text-center">${item.qty}</span>
                            <button onclick="updateDrawerQty(${item.id}, 1)" class="w-5 h-5 flex items-center justify-center bg-slate-100 rounded text-slate-600 hover:bg-slate-200">+</button>
                        </div>
                    </div>
                </div>
            </div>`;
        });

        container.innerHTML = html;
        totalEl.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
    }

    function removeDrawerItem(id) {
        let cart = getCart();
        cart = cart.filter(i => i.id !== id);
        saveCart(cart);
        renderCartDrawer();
    }

    function updateDrawerQty(id, delta) {
        let cart = getCart();
        let item = cart.find(i => i.id === id);
        if (item) {
            let newQty = item.qty + delta;
            if (newQty < 1) newQty = 1;
            if (newQty > item.maxStokBox) {
                showToast('Maksimal stok tercapai!', 'error');
                return;
            }
            item.qty = newQty;
            saveCart(cart);
            renderCartDrawer();
        }
    }

    // Init
    document.addEventListener('DOMContentLoaded', updateCartBadge);
    </script>
</body>
</html>