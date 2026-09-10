<?php
// File: ecommerce/header.php
session_start();
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
    // Option: clear cart when changing branch
    // $_SESSION['cart'] = []; 
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Categories for Mega Menu
$kategoriMenu = $pdo->query("SELECT DISTINCT kategori FROM produk_induk WHERE is_active = 1")->fetchAll(PDO::FETCH_COLUMN);

$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['role'] === 'pelanggan';
$namaPelanggan = $isLoggedIn ? $_SESSION['nama_lengkap'] : '';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ZenCare Medical' : 'ZenCare Medical' ?></title>
    <!-- Tailwind CSS (via CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom Font */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #334155; }
        
        /* Hide scrollbar for smooth cart drawer */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* Mega Menu Transition */
        .mega-menu {
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .mega-menu-trigger:hover .mega-menu, .mega-menu:hover {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        /* Header Scroll Shadow */
        #main-header.scrolled {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="flex flex-col min-h-screen relative">

<!-- ============================================================ -->
<!-- TOP BAR (Dark Navy) -->
<!-- ============================================================ -->
<div class="bg-[#0f2d5a] text-blue-100 text-xs py-2 px-4 sm:px-8 border-b border-white/10 z-50 relative hidden sm:block">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-4">
            <span class="flex items-center gap-1.5 opacity-80 hover:opacity-100 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Melayani Pengiriman Seluruh Indonesia
            </span>
            <span class="w-px h-3 bg-white/20"></span>
            <a href="#" class="flex items-center gap-1.5 opacity-80 hover:opacity-100 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                CS: +62 812-3456-7890
            </a>
        </div>
        <div class="flex items-center gap-4">
            <a href="tentang_kami.php" class="opacity-80 hover:opacity-100 transition">Tentang Kami</a>
            <span class="w-px h-3 bg-white/20"></span>
            <a href="profil.php#riwayat" class="opacity-80 hover:opacity-100 transition">Lacak Pesanan</a>
            <span class="w-px h-3 bg-white/20"></span>
            <a href="../login.php" class="opacity-80 hover:opacity-100 transition font-semibold text-white">Login Staf</a>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MAIN NAVBAR -->
<!-- ============================================================ -->
<header id="main-header" class="bg-white sticky top-0 z-40 transition-shadow duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-8 py-4">
        <div class="flex items-center justify-between gap-4 lg:gap-8">
            
            <!-- Mobile Menu Toggle -->
            <button class="lg:hidden p-2 text-slate-600" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <!-- Brand Logo -->
            <a href="index.php" class="flex items-center gap-2.5 shrink-0 group">
                <div class="w-10 h-10 rounded-xl bg-[#1a75d2] flex items-center justify-center group-hover:scale-105 transition shadow-md shadow-blue-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                </div>
                <div>
                    <h1 class="text-xl font-black tracking-tight text-[#0f2d5a] leading-none">ZenCare</h1>
                    <span class="text-[10px] font-bold text-[#1a75d2] uppercase tracking-wider">Medical Store</span>
                </div>
            </a>

            <!-- Search Bar (Desktop) -->
            <div class="hidden md:flex flex-1 max-w-2xl relative group">
                <form action="kategori.php" method="GET" class="w-full flex">
                    <input type="text" name="q" placeholder="Cari obat, alat kesehatan, atau merk..." class="w-full bg-slate-50 border border-slate-200 text-sm rounded-l-xl px-5 py-3.5 focus:outline-none focus:ring-2 focus:ring-[#1a75d2]/20 focus:border-[#1a75d2] focus:bg-white transition" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button type="submit" class="bg-[#1a75d2] hover:bg-[#0f2d5a] text-white px-6 rounded-r-xl transition flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </form>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                <div class="w-px h-8 bg-slate-200 hidden sm:block"></div>

                <!-- Account -->
                <?php if ($isLoggedIn): ?>
                    <div class="relative group">
                        <a href="profil.php" class="hidden sm:flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-200">
                            <div class="w-9 h-9 rounded-full bg-[#1a75d2]/10 text-[#1a75d2] flex items-center justify-center font-bold">
                                <?= strtoupper(substr($namaPelanggan, 0, 1)) ?>
                            </div>
                            <div class="text-left">
                                <span class="block text-[10px] text-slate-500 font-medium">Pelanggan</span>
                                <span class="block text-sm font-bold text-[#0f2d5a] leading-none"><?= htmlspecialchars($namaPelanggan) ?></span>
                            </div>
                        </a>
                        <div class="absolute right-0 top-full mt-1 w-48 bg-white border border-slate-200 shadow-xl rounded-xl p-2 hidden group-hover:block z-50">
                            <a href="profil.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-[#1a75d2] rounded-lg">Profil & Riwayat</a>
                            <a href="../logout.php" class="block px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 rounded-lg">Keluar</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="../login_customer.php" class="hidden sm:block px-5 py-2.5 rounded-xl border-2 border-slate-200 text-slate-600 font-bold text-sm hover:border-[#1a75d2] hover:text-[#1a75d2] transition">
                        Masuk / Daftar
                    </a>
                <?php endif; ?>

                <!-- Cart Button -->
                <button onclick="toggleCart()" class="relative p-2.5 rounded-xl bg-[#1a75d2]/10 text-[#1a75d2] hover:bg-[#1a75d2] hover:text-white transition group">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span id="cart-count-badge" class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-rose-500 text-white text-[10px] font-black flex items-center justify-center border-2 border-white shadow-sm">0</span>
                </button>
            </div>
        </div>
        
        <!-- Navigation Links (Desktop) -->
        <div class="hidden lg:flex items-center gap-8 mt-4 pt-4 border-t border-slate-100">
            <a href="index.php" class="text-sm font-bold text-[#0f2d5a] hover:text-[#1a75d2] transition">Beranda</a>
            
            <!-- Mega Menu Dropdown for "Kategori" -->
            <div class="relative group mega-menu-trigger pb-4 -mb-4">
                <a href="kategori.php" class="text-sm font-bold text-slate-600 group-hover:text-[#1a75d2] flex items-center gap-1 transition">
                    Kategori Produk
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </a>
                
                <!-- Mega Menu Panel -->
                <div class="mega-menu absolute top-full left-0 w-[600px] bg-white border border-slate-200 shadow-2xl rounded-2xl p-6 z-50 flex gap-6">
                    <div class="flex-1">
                        <h3 class="text-xs font-black uppercase text-slate-400 tracking-wider mb-4">Kategori Medis</h3>
                        <ul class="space-y-3">
                            <?php foreach ($kategoriMenu as $kat): ?>
                            <li>
                                <a href="kategori.php?kategori=<?= urlencode($kat) ?>" class="text-sm font-semibold text-slate-700 hover:text-[#1a75d2] flex items-center gap-2 group/item">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-200 group-hover/item:bg-[#1a75d2] transition"></span>
                                    <?= htmlspecialchars($kat) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="w-[240px] bg-blue-50 rounded-xl p-4 border border-blue-100 flex flex-col justify-end relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-[#1a75d2]/10 rounded-full"></div>
                        <h4 class="text-sm font-black text-[#0f2d5a] mb-1 relative z-10">Promo Alat Kesehatan</h4>
                        <p class="text-xs text-blue-600 mb-4 relative z-10">Diskon khusus untuk pembelian alat kesehatan klinik.</p>
                        <a href="kategori.php?kategori=Alat+Kesehatan" class="text-xs font-bold bg-[#1a75d2] text-white py-2 px-4 rounded-lg text-center hover:bg-[#0f2d5a] transition relative z-10">Lihat Promo</a>
                    </div>
                </div>
            </div>
            
            <a href="kategori.php?q=promo" class="text-sm font-bold text-rose-500 hover:text-rose-600 flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Promo Spesial
            </a>
            <a href="https://wa.me/6281234567890?text=Halo%20Apoteker%20ZenCare,%20saya%20ingin%20konsultasi%20mengenai%20produk%20kesehatan" target="_blank" class="text-sm font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a5.225 5.225 0 00-.571-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347zM12 21a8.96 8.96 0 01-4.588-1.257l-.329-.195-3.411.895.912-3.326-.214-.341A8.961 8.961 0 013 12c0-4.97 4.03-9 9-9s9 4.03 9-9 9-4.03 9-9 9zm0-10.706C8.869 1.294 6.327.031 3.515.031.703.031-1.839 1.294-1.839 4.106c0 2.812 2.542 4.075 5.354 4.075s5.354-1.263 5.354-4.075zM12 2C6.477 2 2 6.477 2 12c0 1.755.454 3.412 1.254 4.856L2 22l5.293-1.189A9.96 9.96 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg>
                Konsultasi Apoteker
            </a>
            <a href="tentang_kami.php" class="text-sm font-bold text-slate-600 hover:text-[#1a75d2] transition">Cara Pembelian</a>
        </div>
    </div>
</header>

<!-- Mobile Menu (Hidden by default) -->
<div id="mobile-menu" class="hidden lg:hidden bg-white border-b border-slate-200 px-4 py-4 space-y-4">
    <form action="kategori.php" method="GET" class="w-full relative">
        <input type="text" name="q" placeholder="Cari..." class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-4 pr-10 py-2.5 focus:outline-none focus:border-[#1a75d2]">
        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </button>
    </form>
    <div class="space-y-2 font-bold text-slate-700">
        <a href="index.php" class="block py-2">Beranda</a>
        <a href="kategori.php" class="block py-2">Semua Kategori</a>
        <?php if ($isLoggedIn): ?>
            <a href="../login_customer.php?logout=1" class="block py-2 text-rose-500">Keluar (<?= htmlspecialchars($namaPelanggan) ?>)</a>
        <?php else: ?>
            <a href="../login_customer.php" class="block py-2 text-[#1a75d2]">Masuk / Daftar</a>
        <?php endif; ?>
    </div>
</div>

<script>
// Header shadow on scroll
window.addEventListener('scroll', () => {
    if(window.scrollY > 10) {
        document.getElementById('main-header').classList.add('scrolled');
    } else {
        document.getElementById('main-header').classList.remove('scrolled');
    }
});
</script>
