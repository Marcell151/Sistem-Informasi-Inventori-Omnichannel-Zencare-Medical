<?php
// File: ecommerce/header.php — Shared Header (Redesign v3, Pharmify-inspired)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

// Init active cabang if not set
if (isset($_GET['cabang_aktif'])) {
    $_SESSION['cabang_ecommerce'] = intval($_GET['cabang_aktif']);
}
$activeCabangId = $_SESSION['cabang_ecommerce'] ?? 1;

// Fetch all cabangs for selector
$cabangs = $pdo->query("SELECT id, nama FROM cabang WHERE is_active = 1")->fetchAll();
// Active cabang info
$cabangAktif = array_filter($cabangs, fn($c) => $c['id'] == $activeCabangId);
$cabangAktif = reset($cabangAktif) ?: ['nama' => 'Pilih Cabang'];

$isLoggedIn = isset($_SESSION['user_id']);
$userNama = $isLoggedIn ? htmlspecialchars($_SESSION['nama_lengkap']) : '';
$userRole = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ZenCare Medical' : 'ZenCare Medical Store' ?></title>
    <meta name="description" content="Toko resmi alat kesehatan dan obat-obatan ZenCare Medical.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { sans: ['Inter', 'sans-serif'] },
            colors: {
              zc: '#1a75d2', zcHv: '#1562b3', zcDark: '#0f2d5a', zcLt: '#e8f2ff',
              zcTxt: '#1e293b', zcMut: '#64748b'
            }
          }
        }
      }
    </script>
    <style>
        /* Smooth scrolling */
        html { scroll-behavior: smooth; }
        /* Glassmorphism utilities */
        .glass-nav { background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(228, 233, 240, 0.8); }
        .glass-dark { background: rgba(15, 45, 90, 0.98); backdrop-filter: blur(8px); }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        /* Mega Menu Dropdown */
        .mega-menu { opacity: 0; visibility: hidden; transform: translateY(10px); transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .group:hover .mega-menu { opacity: 1; visibility: visible; transform: translateY(0); }
    </style>
</head>
<body class="font-sans text-zcTxt bg-[#f8fafc] flex flex-col min-h-screen">

    <!-- ============================================================ -->
    <!-- TOP BAR (Pharmify-style Navy Blue)                           -->
    <!-- ============================================================ -->
    <div class="bg-zcDark text-white text-[11px] font-medium hidden md:block">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 py-2 flex items-center justify-between">
            <div class="flex items-center gap-4 opacity-90">
                <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-blue-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg> Jl. Muharto No.1, Malang</span>
                <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-blue-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg> Bantuan: 0812-3456-7890</span>
            </div>
            
            <div class="flex items-center gap-4">
                <a href="#" class="opacity-80 hover:opacity-100 hover:text-white transition">Kebijakan Pengembalian</a>
                <a href="#" class="opacity-80 hover:opacity-100 hover:text-white transition">Lacak Pesanan</a>
                <!-- Branch Selector -->
                <div class="flex items-center gap-2 pl-4 border-l border-white/20">
                    <svg class="w-3.5 h-3.5 text-blue-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <form method="GET" action="" class="inline-block" id="formCabangTop">
                        <?php foreach($_GET as $k=>$v): if($k!=='cabang_aktif') echo '<input type="hidden" name="'.htmlspecialchars($k).'" value="'.htmlspecialchars($v).'">'; endforeach; ?>
                        <select name="cabang_aktif" onchange="document.getElementById('formCabangTop').submit()" class="bg-transparent font-bold focus:outline-none cursor-pointer hover:text-blue-200">
                            <?php foreach ($cabangs as $c): ?>
                            <option class="text-zcTxt" value="<?= $c['id'] ?>" <?= $c['id'] == $activeCabangId ? 'selected' : '' ?>><?= htmlspecialchars($c['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- MAIN NAVIGATION (Glassmorphism & Mega Menu)                  -->
    <!-- ============================================================ -->
    <header class="glass-nav sticky top-0 z-50 shadow-sm transition-all duration-300" id="mainHeader">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 h-20 flex items-center justify-between gap-4 lg:gap-8">
            
            <!-- Logo -->
            <a href="index.php" class="flex items-center gap-3 group shrink-0">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-zc to-zcHv flex items-center justify-center shadow-md group-hover:shadow-lg transition-all duration-300 group-hover:scale-105">
                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                </div>
                <div class="hidden sm:block">
                    <span class="text-lg font-extrabold text-zcTxt tracking-tight block leading-tight group-hover:text-zc transition">ZenCare Medical</span>
                    <span class="text-[10px] font-semibold text-zcMut block tracking-widest uppercase">Toko Kesehatan Resmi</span>
                </div>
            </a>

            <!-- Search Bar (Centered) -->
            <div class="flex-1 max-w-2xl hidden md:block">
                <form action="kategori.php" method="GET" class="relative group">
                    <svg class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2 group-focus-within:text-zc transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input type="text" name="q" placeholder="Cari obat, alat kesehatan, merk..." 
                           class="w-full pl-11 pr-4 py-3 bg-slate-100 hover:bg-slate-200/60 focus:bg-white border-2 border-transparent focus:border-zc/30 rounded-2xl text-sm font-medium transition-all duration-200 outline-none shadow-inner-sm placeholder-slate-400">
                    <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white text-zcTxt font-bold text-[11px] px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm hover:border-zc hover:text-zc transition">Cari</button>
                </form>
            </div>

            <!-- Main Nav Links -->
            <nav class="hidden lg:flex items-center gap-1.5 shrink-0">
                <a href="index.php" class="px-4 py-2.5 rounded-xl text-sm font-bold text-zc bg-zcLt transition">Beranda</a>
                
                <!-- Mega Menu Trigger -->
                <div class="group relative px-2 py-2.5">
                    <a href="kategori.php" class="flex items-center gap-1.5 text-sm font-semibold text-zcTxt hover:text-zc transition">
                        Produk <svg class="w-4 h-4 opacity-50 group-hover:rotate-180 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                    </a>
                    
                    <!-- Mega Menu Content -->
                    <div class="mega-menu absolute top-full left-1/2 -translate-x-1/2 mt-1 w-[600px] bg-white rounded-3xl shadow-2xl border border-slate-100 p-6 flex gap-6">
                        <div class="flex-1">
                            <h4 class="text-xs font-extrabold uppercase tracking-widest text-slate-400 mb-4">Kategori Medis</h4>
                            <ul class="grid grid-cols-2 gap-x-4 gap-y-3">
                                <li><a href="kategori.php?kategori=Obat" class="flex items-center gap-2 text-sm font-semibold text-zcTxt hover:text-zc group/item"><div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-zc group-hover/item:scale-110 transition"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m10.5 20.5 10-10a4.95 4.95 0 10-7-7l-10 10a4.95 4.95 0 107 7z"/></svg></div> Obat-obatan</a></li>
                                <li><a href="kategori.php?kategori=Alat+Kesehatan" class="flex items-center gap-2 text-sm font-semibold text-zcTxt hover:text-zc group/item"><div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover/item:scale-110 transition"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div> Alat Kesehatan</a></li>
                                <li><a href="kategori.php" class="flex items-center gap-2 text-sm font-semibold text-zcTxt hover:text-zc group/item"><div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 group-hover/item:scale-110 transition"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></div> Vitamin & Sup.</a></li>
                                <li><a href="kategori.php" class="flex items-center gap-2 text-sm font-bold text-zc hover:underline mt-2">Lihat Semua &rarr;</a></li>
                            </ul>
                        </div>
                        <div class="w-48 rounded-2xl bg-gradient-to-br from-zc to-zcHv p-5 text-white flex flex-col justify-end relative overflow-hidden">
                            <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                            <span class="text-xs font-bold bg-white/20 px-2 py-1 rounded w-fit mb-2 backdrop-blur-sm">PROMO</span>
                            <span class="font-extrabold leading-tight mb-1">Diskon Alkes<br>hingga 20%</span>
                            <a href="kategori.php" class="text-xs font-medium text-blue-200 hover:text-white transition">Beli Sekarang &rarr;</a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Right Actions -->
            <div class="flex items-center gap-3 lg:gap-4 shrink-0">
                <?php if ($isLoggedIn && $userRole === 'pelanggan'): ?>
                    <a href="akun.php" class="hidden md:flex items-center gap-2 text-sm font-semibold text-zcTxt hover:text-zc transition">
                        <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200"><svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
                        <span>Hai, <?= explode(' ', $userNama)[0] ?></span>
                    </a>
                <?php else: ?>
                    <div class="hidden sm:flex items-center gap-2">
                        <a href="../login_customer.php" class="text-sm font-bold text-zcTxt hover:text-zc px-3 py-2 transition">Masuk</a>
                        <a href="../register.php" class="text-sm font-bold bg-zc text-white px-4 py-2 rounded-xl shadow-sm hover:bg-zcHv hover:shadow transition">Daftar</a>
                    </div>
                <?php endif; ?>

                <!-- Cart Button -->
                <button onclick="toggleCart()" class="relative flex items-center gap-2 bg-zcDark hover:bg-[#0b1f3a] text-white px-4 py-2.5 rounded-xl shadow-md transition group">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.55L23 6H6"/></svg>
                    <span class="hidden sm:block text-sm font-bold">Keranjang</span>
                    <span id="cartCountBadge" class="absolute -top-2 -right-2 bg-rose-500 text-white text-[10px] font-extrabold w-5 h-5 flex items-center justify-center rounded-full border-2 border-white shadow-sm scale-0 transition-transform">0</span>
                </button>
                
                <!-- Mobile Menu Toggle -->
                <button onclick="document.getElementById('mobileMenu').classList.toggle('hidden')" class="lg:hidden p-2 text-zcTxt hover:bg-slate-100 rounded-xl transition">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Menu (Hidden by default) -->
    <div id="mobileMenu" class="hidden lg:hidden bg-white border-b border-slate-200 px-4 py-4 space-y-4">
        <form action="kategori.php" method="GET" class="relative">
            <input type="text" name="q" placeholder="Cari obat, alkes..." class="w-full pl-10 pr-4 py-2.5 bg-slate-100 border-none rounded-xl text-sm outline-none focus:ring-2 focus:ring-zc/30">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        </form>
        <div class="flex flex-col gap-2">
            <a href="index.php" class="px-4 py-2.5 bg-zcLt text-zc rounded-xl font-bold text-sm">Beranda</a>
            <a href="kategori.php" class="px-4 py-2.5 text-zcTxt hover:bg-slate-50 rounded-xl font-bold text-sm">Semua Produk</a>
            <a href="kategori.php?kategori=Obat" class="px-4 py-2.5 text-zcTxt hover:bg-slate-50 rounded-xl font-bold text-sm">Kategori Obat</a>
            <a href="kategori.php?kategori=Alat+Kesehatan" class="px-4 py-2.5 text-zcTxt hover:bg-slate-50 rounded-xl font-bold text-sm">Alat Kesehatan</a>
        </div>
        <div class="pt-4 border-t border-slate-100">
            <?php if ($isLoggedIn && $userRole === 'pelanggan'): ?>
                <a href="akun.php" class="px-4 py-2.5 block text-zcTxt font-bold text-sm">Profil Akun Saya</a>
                <a href="../logout.php" class="px-4 py-2.5 block text-rose-600 font-bold text-sm">Keluar</a>
            <?php else: ?>
                <a href="../login_customer.php" class="block text-center bg-slate-100 text-zcTxt font-bold py-2.5 rounded-xl mb-2">Masuk</a>
                <a href="../register.php" class="block text-center bg-zc text-white font-bold py-2.5 rounded-xl">Daftar Akun Baru</a>
            <?php endif; ?>
        </div>
    </div>
