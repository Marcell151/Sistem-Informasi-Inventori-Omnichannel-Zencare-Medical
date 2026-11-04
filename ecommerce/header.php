<?php
// File: ecommerce/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

// Initialize session variables if not exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (!isset($_SESSION['cabang_ecommerce'])) {
    $_SESSION['cabang_ecommerce'] = 1; // Default Cabang
}

// Branch Selection Handler
if (isset($_GET['cabang_ecommerce'])) {
    $_SESSION['cabang_ecommerce'] = intval($_GET['cabang_ecommerce']);
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Categories for Mega Menu
$kategoriMenu = $pdo->query("SELECT DISTINCT kategori FROM produk_induk WHERE is_active = 1")->fetchAll(PDO::FETCH_COLUMN);

// Group sample parent products for the Mega Menu columns
$megaStmt = $pdo->query("
    SELECT i.id, i.nama_produk, i.kategori, MIN(v.id) AS id_variasi
    FROM produk_induk i
    JOIN produk_variasi v ON v.id_produk_induk = i.id
    WHERE i.is_active = 1 AND v.is_active = 1 AND v.tampil_di_online = 1
    GROUP BY i.id, i.nama_produk, i.kategori
    ORDER BY i.nama_produk ASC
");
$allMegaItems = $megaStmt->fetchAll(PDO::FETCH_ASSOC);

$megaObat = array_values(array_filter($allMegaItems, fn($x) => $x['kategori'] === 'Obat'));
$megaAlkes = array_values(array_filter($allMegaItems, fn($x) => $x['kategori'] === 'Alat Kesehatan'));

$megaCol1 = array_slice($megaObat, 0, 6);
$megaCol2 = array_slice($megaObat, 6, 6);
$megaCol3 = array_slice($megaObat, 12, 6);
$megaCol4 = array_slice($megaAlkes, 0, 6);

$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['role'] === 'pelanggan';
$namaPelanggan = $isLoggedIn ? $_SESSION['nama_lengkap'] : '';
$currentScript = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ZenCare Medical' : 'ZenCare Medical' ?></title>
    <!-- Tailwind CSS (via CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'] },
            colors: {
              zc:    '#1a75d2',
              zcHv:  '#0f2d5a',
              zcNavy:'#0f2d5a',
              zcLt:  '#eef6fb',
              zcEm:  '#059669',
              zcBrd: '#e4e9f0',
              zcTxt: '#1e293b',
              zcMut: '#64748b',
            }
          }
        }
      }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');
        body { 
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; 
            background-color: #ffffff; 
            color: #1e293b; 
            -webkit-tap-highlight-color: transparent;
        }
        
        /* Prevent horizontal overflow on all devices including foldables */
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        /* Prevent iOS Safari auto-zoom on input focus */
        @media screen and (max-width: 640px) {
            input[type="text"], input[type="number"], input[type="email"], input[type="tel"], select, textarea {
                font-size: 16px !important;
            }
        }
        
        /* Hide scrollbar for smooth cart drawer & mobile horizontal tabs */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* Mega Menu Transition */
        .mega-menu {
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px);
            transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .mega-menu-trigger:hover .mega-menu, .mega-menu:hover {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        /* Header Scroll Shadow */
        #main-header.scrolled {
            box-shadow: 0 4px 20px -2px rgba(15, 45, 90, 0.08);
        }

        /* Safe area bottom padding for iPhone with home indicator */
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 16px);
        }

        /* Molecular Medical Pattern Banner */
        .pharmify-banner {
            background-color: #0b2948;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(26, 117, 210, 0.28) 0%, transparent 50%),
                radial-gradient(circle at 80% 30%, rgba(14, 165, 233, 0.2) 0%, transparent 45%),
                url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cg stroke='%2338bdf8' stroke-width='0.6' stroke-opacity='0.18' fill='none'%3E%3Cpolygon points='50,8 85,28 85,68 50,88 15,68 15,28'/%3E%3Cline x1='50' y1='8' x2='50' y2='0'/%3E%3Cline x1='85' y1='28' x2='100' y2='20'/%3E%3Cline x1='85' y1='68' x2='100' y2='76'/%3E%3Ccircle cx='50' cy='8' r='2' fill='%2338bdf8' fill-opacity='0.25'/%3E%3Ccircle cx='85' cy='28' r='2' fill='%2338bdf8' fill-opacity='0.25'/%3E%3Ccircle cx='15' cy='68' r='2' fill='%2338bdf8' fill-opacity='0.25'/%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="flex flex-col min-h-screen relative bg-white">

<!-- ============================================================ -->
<!-- TOP UTILITY BAR (Deep Navy - Pharmify Style)                 -->
<!-- ============================================================ -->
<div class="bg-[#0f2d5a] text-white text-xs py-2.5 px-4 sm:px-8 z-50 relative hidden sm:block">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <!-- Left: Social & Store Hours -->
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <a href="https://wa.me/6281234567890" target="_blank" title="WhatsApp Resmi" class="w-5 h-5 rounded-full bg-white text-[#0f2d5a] flex items-center justify-center hover:bg-blue-100 transition">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a5.225 5.225 0 00-.571-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347zM12 2C6.477 2 2 6.477 2 12c0 1.755.454 3.412 1.254 4.856L2 22l5.293-1.189A9.96 9.96 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg>
                </a>
                <a href="tentang_kami.php" title="Informasi Apotek" class="w-5 h-5 rounded-full bg-white text-[#0f2d5a] flex items-center justify-center hover:bg-blue-100 transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </a>
                <a href="cara_pembelian.php" title="Panduan Belanja" class="w-5 h-5 rounded-full bg-white text-[#0f2d5a] flex items-center justify-center hover:bg-blue-100 transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </a>
            </div>
            <span class="w-px h-3.5 bg-white/20 mx-1"></span>
            <span class="text-[11px] text-blue-100 font-medium">Distributor Resmi Obat &amp; Alat Kesehatan</span>
        </div>

        <!-- Right: Address, Email & Staff Login -->
        <div class="flex items-center gap-3 lg:gap-5 text-[11px]">
            <span class="hidden lg:flex items-center gap-1.5 text-blue-100">
                <svg class="w-3.5 h-3.5 text-sky-300 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                Jl. Muharto No. 1, Kota Malang, Jawa Timur
            </span>
            <span class="w-px h-3.5 bg-white/20 hidden lg:block"></span>
            <a href="mailto:cs@zencare.id" class=" flex items-center gap-1.5 text-blue-100 hover:text-white transition">
                <svg class="w-3.5 h-3.5 text-sky-300 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                cs@zencare.id
            </a>
            <span class="w-px h-3.5 bg-white/20"></span>
            <a href="../login.php" class="text-blue-200 hover:text-white font-semibold transition whitespace-nowrap">Portal Staf</a>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MAIN NAVBAR (Single-Row Pharmify Layout)                     -->
<!-- ============================================================ -->
<header id="main-header" class="bg-white sticky top-0 z-40 border-b border-slate-100 transition-shadow duration-300">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-2 sm:gap-4">
            
            <!-- Left: Mobile Menu Button + Desktop Navigation Links -->
            <div class="flex items-center gap-1.5 sm:gap-5 lg:gap-6">
                <button type="button" aria-label="Buka Menu" class="lg:hidden p-1.5 sm:p-2 -ml-1 text-slate-700 hover:text-[#0f2d5a] rounded-md transition" onclick="toggleMobileMenu()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <nav class="hidden lg:flex items-center gap-5 xl:gap-7 text-[13px] font-semibold whitespace-nowrap">
                    <a href="index.php" class="<?= $currentScript === 'index.php' ? 'text-[#1a75d2] font-bold' : 'text-slate-800 hover:text-[#1a75d2]' ?> transition py-7">
                        Beranda
                    </a>

                    <!-- Mega Menu Trigger: Katalog -->
                    <div class="mega-menu-trigger static py-7">
                        <a href="kategori.php" class="<?= ($currentScript === 'kategori.php' || $currentScript === 'produk.php') ? 'text-[#1a75d2] font-bold' : 'text-slate-800 hover:text-[#1a75d2]' ?> flex items-center gap-1 transition">
                            Katalog
                            <svg class="w-3.5 h-3.5 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </a>

                        <!-- Full-Width Pharmify Mega Menu Panel -->
                        <div class="mega-menu absolute left-0 right-0 top-full bg-white border-t border-b border-slate-200 shadow-2xl z-50">
                            <div class="max-w-7xl mx-auto px-8 py-7">
                                <div class="grid grid-cols-12 gap-8">
                                    <!-- Column 1: Left Category Selector Box (Pharmify Style) -->
                                    <div class="col-span-3 bg-[#eef6fb] rounded-md overflow-hidden flex flex-col">
                                        <a href="kategori.php?kategori=Obat" class="flex items-center justify-between px-4 py-3 bg-[#0f2d5a] text-white text-xs font-bold tracking-wide">
                                            <span>Obat &amp; Farmasi</span>
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                        </a>
                                        <a href="kategori.php?kategori=Alat+Kesehatan" class="flex items-center justify-between px-4 py-3 text-slate-700 hover:bg-[#dcecf8] hover:text-[#0f2d5a] text-xs font-medium border-b border-slate-200/60 transition">
                                            <span>Alat Kesehatan Resmi</span>
                                            <svg class="w-3.5 h-3.5 opacity-40" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                        </a>
                                        <a href="kategori.php?q=Vitamin" class="flex items-center justify-between px-4 py-3 text-slate-700 hover:bg-[#dcecf8] hover:text-[#0f2d5a] text-xs font-medium border-b border-slate-200/60 transition">
                                            <span>Vitamin &amp; Suplemen</span>
                                            <svg class="w-3.5 h-3.5 opacity-40" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                        </a>
                                        <a href="kategori.php?q=Sirup" class="flex items-center justify-between px-4 py-3 text-slate-700 hover:bg-[#dcecf8] hover:text-[#0f2d5a] text-xs font-medium border-b border-slate-200/60 transition">
                                            <span>Sirup &amp; Obat Anak</span>
                                            <svg class="w-3.5 h-3.5 opacity-40" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                        </a>
                                        <a href="kategori.php" class="flex items-center justify-between px-4 py-3 text-[#1a75d2] hover:bg-[#dcecf8] text-xs font-bold transition mt-auto">
                                            <span>Lihat Seluruh Koleksi</span>
                                            <span>&rarr;</span>
                                        </a>
                                    </div>

                                    <!-- Column 2: Obat Umum & Tablet -->
                                    <div class="col-span-2">
                                        <h4 class="text-xs font-bold text-slate-900 mb-3 pb-1.5 border-b border-slate-100">Obat Tablet &amp; Kaplet</h4>
                                        <ul class="space-y-2 text-xs text-slate-600">
                                            <?php foreach ($megaCol1 as $m): ?>
                                                <li><a href="produk.php?id=<?= $m['id_variasi'] ?>" class="hover:text-[#1a75d2] transition block truncate"><?= htmlspecialchars($m['nama_produk']) ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>

                                    <!-- Column 3: Sirup & Perawatan -->
                                    <div class="col-span-2">
                                        <h4 class="text-xs font-bold text-slate-900 mb-3 pb-1.5 border-b border-slate-100">Sirup &amp; Perawatan</h4>
                                        <ul class="space-y-2 text-xs text-slate-600">
                                            <?php foreach ($megaCol2 as $m): ?>
                                                <li><a href="produk.php?id=<?= $m['id_variasi'] ?>" class="hover:text-[#1a75d2] transition block truncate"><?= htmlspecialchars($m['nama_produk']) ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>

                                    <!-- Column 4: Vitamin & Herbal -->
                                    <div class="col-span-2">
                                        <h4 class="text-xs font-bold text-slate-900 mb-3 pb-1.5 border-b border-slate-100">Suplemen &amp; Lainnya</h4>
                                        <ul class="space-y-2 text-xs text-slate-600">
                                            <?php foreach ($megaCol3 as $m): ?>
                                                <li><a href="produk.php?id=<?= $m['id_variasi'] ?>" class="hover:text-[#1a75d2] transition block truncate"><?= htmlspecialchars($m['nama_produk']) ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>

                                    <!-- Column 5: Alat Kesehatan -->
                                    <div class="col-span-3">
                                        <h4 class="text-xs font-bold text-slate-900 mb-3 pb-1.5 border-b border-slate-100">Alat Kesehatan &amp; BMHP</h4>
                                        <ul class="space-y-2 text-xs text-slate-600">
                                            <?php foreach ($megaCol4 as $m): ?>
                                                <li><a href="produk.php?id=<?= $m['id_variasi'] ?>" class="hover:text-[#1a75d2] transition block truncate"><?= htmlspecialchars($m['nama_produk']) ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="tentang_kami.php" class="<?= $currentScript === 'tentang_kami.php' ? 'text-[#1a75d2] font-bold' : 'text-slate-800 hover:text-[#1a75d2]' ?> transition py-7">
                        Tentang Kami
                    </a>

                    <a href="cara_pembelian.php" class="<?= $currentScript === 'cara_pembelian.php' ? 'text-[#1a75d2] font-bold' : 'text-slate-800 hover:text-[#1a75d2]' ?> transition py-7">
                        Cara Belanja
                    </a>

                    <a href="profil.php" class="<?= $currentScript === 'profil.php' ? 'text-[#1a75d2] font-bold' : 'text-slate-800 hover:text-[#1a75d2]' ?> transition py-7">
                        Pesanan
                    </a>
                </nav>
            </div>

            <!-- Center: Brand Logo (Pharmify Centered Style) -->
            <a href="index.php" class="flex items-center gap-2 sm:gap-2.5 shrink-0 group">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center overflow-hidden shadow-2xs">
                    <img src="/inventory_zencare/gambar/Logo.png" alt="ZenCare Logo" class="w-full h-full object-contain rounded-full">
                </div>
                <div class="flex flex-col">
                    <span class="text-lg sm:text-xl font-extrabold tracking-tight text-[#0f2d5a] leading-none">ZenCare</span>
                    <span class="text-[8px] sm:text-[9px] font-bold text-[#1a75d2] uppercase tracking-widest mt-0.5">Medical Store</span>
                </div>
            </a>

            <!-- Right: Pill Search Input & Action Icons -->
            <div class="flex items-center gap-1 sm:gap-3 lg:gap-4">
                <!-- Pill Search Bar (Desktop) -->
                <form action="kategori.php" method="GET" class="hidden md:flex items-center relative w-40 lg:w-48 xl:w-64">
                    <input type="text" name="q" placeholder="Cari obat atau alkes..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                           class="w-full bg-white border border-slate-300 rounded-full pl-3.5 pr-8 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#0f2d5a] transition">
                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-600 hover:text-[#0f2d5a] transition" title="Cari">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </form>

                <!-- Mobile Quick Search Trigger Button -->
                <button type="button" onclick="toggleMobileSearch()" class="md:hidden p-1.5 text-slate-700 hover:text-[#0f2d5a] transition" title="Cari Produk">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>

                <!-- Action Icons (Orders, Cart Drawer, Account) -->
                <div class="flex items-center gap-1 sm:gap-2.5 text-slate-800">
                    <!-- Order Tracking Icon -->
                    <a href="profil.php" title="Riwayat Pesanan Saya" class="hidden min-[360px]:inline-flex relative p-1.5 hover:text-[#1a75d2] transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </a>

                    <!-- Cart Bag Icon -->
                    <button type="button" onclick="toggleCart()" title="Keranjang Belanja" class="relative p-1.5 hover:text-[#1a75d2] transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span id="cart-count-badge" class="absolute -top-1 -right-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-[#0f2d5a] text-white text-[10px] font-bold flex items-center justify-center">0</span>
                    </button>

                    <!-- User Account Icon / Dropdown -->
                    <?php if ($isLoggedIn): ?>
                        <div class="relative group">
                            <a href="profil.php" class="flex items-center gap-1.5 p-1.5 hover:text-[#1a75d2] transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="hidden xl:inline text-xs font-bold text-[#0f2d5a] max-w-[100px] truncate"><?= htmlspecialchars($namaPelanggan) ?></span>
                            </a>
                            <div class="absolute right-0 top-full pt-2 w-48 hidden group-hover:block z-50">
                                <div class="bg-white border border-slate-200 shadow-xl rounded-lg py-1.5 text-xs">
                                    <div class="px-4 py-2 border-b border-slate-100">
                                        <span class="block text-[10px] text-slate-400">Akun Pelanggan</span>
                                        <strong class="text-slate-800 truncate block"><?= htmlspecialchars($namaPelanggan) ?></strong>
                                    </div>
                                    <a href="profil.php" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 hover:text-[#1a75d2] font-medium">Profil &amp; Pesanan</a>
                                    <a href="cart.php" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 hover:text-[#1a75d2] font-medium">Keranjang Penuh</a>
                                    <a href="../logout.php" class="block px-4 py-2 text-rose-600 hover:bg-rose-50 font-semibold border-t border-slate-100">Keluar</a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="../login_customer.php" title="Masuk / Daftar Akun" class="flex items-center gap-1.5 p-1.5 hover:text-[#1a75d2] transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="hidden sm:inline text-xs font-bold text-[#0f2d5a]">Masuk</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- Mobile Quick Search Bar (Collapsible) -->
    <div id="mobile-search-bar" class="hidden md:hidden px-4 pb-3 pt-1 border-t border-slate-100 bg-white">
        <form action="kategori.php" method="GET" class="relative">
            <input type="text" name="q" id="mobile-search-input" placeholder="Cari obat, variasi, atau alkes..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                   class="w-full bg-[#f8fafc] border border-slate-300 rounded-full pl-4 pr-10 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#0f2d5a]">
            <button type="submit" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </button>
        </form>
    </div>
</header>

<!-- Mobile Menu Overlay & Drawer -->
<div id="mobile-menu-overlay" class="hidden fixed inset-0 bg-[#0f2d5a]/40 z-50 backdrop-blur-xs transition-opacity duration-300 opacity-0" onclick="toggleMobileMenu()"></div>
<div id="mobile-menu" class="fixed top-0 left-0 bottom-0 w-[82%] max-w-xs bg-white shadow-2xl z-50 transform -translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <!-- Drawer Header -->
    <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between bg-[#f8fafc]">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center overflow-hidden">
                <img src="/inventory_zencare/gambar/Logo.png" alt="ZenCare Logo" class="w-full h-full object-contain">
            </div>
            <span class="text-sm font-extrabold text-[#0f2d5a]">Menu ZenCare</span>
        </div>
        <button type="button" onclick="toggleMobileMenu()" class="p-1.5 text-slate-400 hover:text-[#0f2d5a] transition cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <!-- Drawer Navigation Links -->
    <div class="flex-1 overflow-y-auto px-4 py-4 space-y-1 text-xs font-semibold text-slate-700 divide-y divide-slate-100">
        <div class="space-y-1 pb-3">
            <a href="index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-md hover:bg-[#eef6fb] hover:text-[#0f2d5a] transition <?= $currentScript === 'index.php' ? 'bg-[#eef6fb] text-[#1a75d2] font-bold' : '' ?>">
                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Beranda
            </a>
            <a href="kategori.php" class="flex items-center gap-3 px-3 py-2.5 rounded-md hover:bg-[#eef6fb] hover:text-[#0f2d5a] transition <?= ($currentScript === 'kategori.php' && empty($_GET['kategori'])) ? 'bg-[#eef6fb] text-[#1a75d2] font-bold' : '' ?>">
                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Semua Katalog Produk
            </a>
            <a href="kategori.php?kategori=Obat" class="flex items-center gap-3 px-3 py-2 rounded-md pl-9 text-slate-500 hover:bg-[#eef6fb] hover:text-[#0f2d5a] transition">
                &bull; Kategori Obat &amp; Farmasi
            </a>
            <a href="kategori.php?kategori=Alat+Kesehatan" class="flex items-center gap-3 px-3 py-2 rounded-md pl-9 text-slate-500 hover:bg-[#eef6fb] hover:text-[#0f2d5a] transition">
                &bull; Alat Kesehatan &amp; BMHP
            </a>
        </div>

        <div class="space-y-1 py-3">
            <a href="profil.php" class="flex items-center gap-3 px-3 py-2.5 rounded-md hover:bg-[#eef6fb] hover:text-[#0f2d5a] transition <?= $currentScript === 'profil.php' ? 'bg-[#eef6fb] text-[#1a75d2] font-bold' : '' ?>">
                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Riwayat Pesanan Saya
            </a>
            <a href="cart.php" class="flex items-center gap-3 px-3 py-2.5 rounded-md hover:bg-[#eef6fb] hover:text-[#0f2d5a] transition <?= $currentScript === 'cart.php' ? 'bg-[#eef6fb] text-[#1a75d2] font-bold' : '' ?>">
                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Keranjang Belanja
            </a>
        </div>

        <div class="space-y-1 py-3">
            <a href="tentang_kami.php" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-[#eef6fb] hover:text-[#0f2d5a] transition">
                Tentang ZenCare
            </a>
            <a href="cara_pembelian.php" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-[#eef6fb] hover:text-[#0f2d5a] transition">
                Cara Belanja &amp; Pengiriman
            </a>
            <a href="../login.php" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-[#eef6fb] hover:text-[#0f2d5a] transition text-slate-500">
                Portal Staf &amp; Gudang
            </a>
        </div>

        <div class="pt-3">
            <?php if ($isLoggedIn): ?>
                <div class="p-3 bg-slate-50 rounded mb-2">
                    <span class="block text-[10px] text-slate-400">Masuk sebagai</span>
                    <strong class="text-[#0f2d5a] block truncate"><?= htmlspecialchars($namaPelanggan) ?></strong>
                </div>
                <a href="../logout.php" class="block text-center py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded transition">
                    Keluar dari Akun
                </a>
            <?php else: ?>
                <a href="../login_customer.php" class="block text-center py-2.5 bg-[#0f2d5a] hover:bg-[#1a75d2] text-white font-bold rounded transition">
                    Masuk / Daftar Akun
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleMobileMenu() {
    const overlay = document.getElementById('mobile-menu-overlay');
    const drawer = document.getElementById('mobile-menu');
    const isOpen = !drawer.classList.contains('-translate-x-full');

    if (isOpen) {
        drawer.classList.add('-translate-x-full');
        overlay.classList.add('opacity-0');
        setTimeout(() => overlay.classList.add('hidden'), 300);
        document.body.style.overflow = '';
    } else {
        overlay.classList.remove('hidden');
        setTimeout(() => overlay.classList.remove('opacity-0'), 10);
        drawer.classList.remove('-translate-x-full');
        document.body.style.overflow = 'hidden';
    }
}

function toggleMobileSearch() {
    const s = document.getElementById('mobile-search-bar');
    if (!s) return;
    s.classList.toggle('hidden');
    if (!s.classList.contains('hidden')) {
        const inp = document.getElementById('mobile-search-input');
        if (inp) inp.focus();
    }
}

window.addEventListener('scroll', () => {
    const hdr = document.getElementById('main-header');
    if (!hdr) return;
    if (window.scrollY > 10) {
        hdr.classList.add('scrolled');
    } else {
        hdr.classList.remove('scrolled');
    }
});
</script>
