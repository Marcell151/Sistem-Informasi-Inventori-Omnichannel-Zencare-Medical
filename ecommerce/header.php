<?php
// File: ecommerce/header.php
// Shared Header & Navigation for ZenCare Medical E-Commerce
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

// Active branch handling
if (isset($_GET['set_cabang'])) {
    $_SESSION['id_cabang'] = intval($_GET['set_cabang']);
}
if (!isset($_SESSION['id_cabang']) || $_SESSION['id_cabang'] <= 0) {
    $_SESSION['id_cabang'] = 1;
}
$activeCabangId = $_SESSION['id_cabang'];

// Fetch branch info
$stmtCabangAktif = $pdo->prepare("SELECT * FROM cabang WHERE id = ? AND is_active = 1");
$stmtCabangAktif->execute([$activeCabangId]);
$cabangAktif = $stmtCabangAktif->fetch();

$daftarCabang = $pdo->query("SELECT * FROM cabang WHERE is_active = 1 ORDER BY id ASC")->fetchAll();

// Web CMS Settings
$stmtWeb = $pdo->query("SELECT * FROM pengaturan_web WHERE id=1");
$webCfg  = $stmtWeb->fetch() ?: [];
$namaToko = $webCfg['nama_toko'] ?? 'ZenCare Medical';
$logoUrl  = $webCfg['logo_url']  ?? '';

// If logo URL is empty, check for local image
if (empty($logoUrl) && file_exists(__DIR__ . '/../gambar/Logo.jfif')) {
    $logoUrl = '../gambar/Logo.jfif';
}

// User session info
$isLoggedIn = isset($_SESSION['user_id']);
$userRole   = $_SESSION['role'] ?? '';
$userName   = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Pelanggan';

// Helper for active navigation
$currentScript = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?><?= htmlspecialchars($namaToko) ?></title>
    <meta name="description" content="Distributor resmi alat kesehatan dan obat-obatan standar medis bergaransi resmi.">
    
    <!-- Tailwind CSS (ZenCare Medical Palette) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { sans: ['Inter', 'sans-serif'] },
            colors: {
              zc:    '#1a75d2',
              zcHv:  '#1562b3',
              zcLt:  '#e8f2ff',
              zcEm:  '#059669',
              zcBrd: '#e4e9f0',
              zcTxt: '#1e293b',
              zcMut: '#64748b',
              zcNavy: '#0f2d5a'
            }
          }
        }
      }
    </script>
    <style>
        * { box-sizing: border-box; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        @keyframes pop { 0%,100%{transform:scale(1)} 50%{transform:scale(1.3)} }
        .pop { animation: pop .25s ease; }
    </style>
</head>
<body class="bg-[#f0f4f9] text-zcTxt font-sans antialiased min-h-screen flex flex-col">

    <!-- Top Announcement Bar -->
    <div class="bg-zcNavy text-white text-xs py-2 px-4 border-b border-blue-900/40">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 font-semibold text-blue-100">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Toko Resmi Alat Kesehatan &amp; Obat
                </span>
                <span class="text-blue-200/60 hidden md:inline">|</span>
                <span class="text-blue-100/80 hidden md:inline">Pengiriman ke Seluruh Indonesia &bull; Izin Resmi Kemenkes</span>
            </div>
            <div class="flex items-center gap-4">
                <!-- Branch Selector -->
                <form method="GET" class="flex items-center gap-1.5 text-blue-100">
                    <svg class="w-3.5 h-3.5 text-blue-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span>Cabang Stok:</span>
                    <select name="set_cabang" onchange="this.form.submit()" class="bg-blue-950/80 text-white text-[11px] font-semibold border border-blue-800 rounded px-2 py-0.5 focus:outline-none focus:border-blue-400 cursor-pointer">
                        <?php foreach ($daftarCabang as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $activeCabangId == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <header class="bg-white border-b border-zcBrd sticky top-0 z-50 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 h-16 flex items-center justify-between gap-4">
            
            <!-- Brand Logo -->
            <a href="index.php" class="flex items-center gap-3 shrink-0 group">
                <?php if ($logoUrl): ?>
                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="ZenCare Logo" class="h-9 w-auto object-contain transition-transform group-hover:scale-105">
                <?php else: ?>
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-zc to-zcHv flex items-center justify-center text-white shadow-xs">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                    </div>
                <?php endif; ?>
                <div>
                    <span class="text-base font-extrabold text-zcTxt leading-none block tracking-tight group-hover:text-zc transition-colors"><?= htmlspecialchars($namaToko) ?></span>
                    <span class="text-xs text-zcMut font-medium">Toko Kesehatan Resmi</span>
                </div>
            </a>

            <!-- Desktop Nav Links -->
            <nav class="hidden lg:flex items-center gap-1 text-sm font-semibold">
                <a href="index.php" class="px-3.5 py-2 rounded-xl transition-colors <?= $currentScript === 'index.php' ? 'text-zc bg-zcLt font-bold' : 'text-zcMut hover:text-zcTxt hover:bg-slate-50' ?>">
                    Beranda
                </a>
                <a href="kategori.php" class="px-3.5 py-2 rounded-xl transition-colors <?= $currentScript === 'kategori.php' ? 'text-zc bg-zcLt font-bold' : 'text-zcMut hover:text-zcTxt hover:bg-slate-50' ?>">
                    Kategori
                </a>
                <?php if ($isLoggedIn): ?>
                <a href="profil.php" class="px-3.5 py-2 rounded-xl transition-colors <?= $currentScript === 'profil.php' ? 'text-zc bg-zcLt font-bold' : 'text-zcMut hover:text-zcTxt hover:bg-slate-50' ?>">
                    Pesanan Saya
                </a>
                <?php endif; ?>
            </nav>

            <!-- Search Bar -->
            <div class="flex-1 max-w-sm hidden md:block">
                <form action="kategori.php" method="GET" class="relative">
                    <svg class="w-4 h-4 text-zcMut absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Cari nama produk, kategori..."
                        class="w-full text-sm pl-9 pr-4 py-2 border border-zcBrd rounded-xl bg-slate-50/70 focus:outline-none focus:border-zc focus:bg-white transition-all shadow-xs">
                </form>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-2.5 shrink-0">
                
                <?php if ($isLoggedIn): ?>
                    <!-- Profile Dropdown Link -->
                    <a href="profil.php" class="flex items-center gap-2 border border-zcBrd px-3 py-1.5 rounded-xl bg-slate-50 hover:bg-slate-100/80 transition-all text-xs">
                        <div class="w-6 h-6 rounded-full bg-zc text-white font-bold text-[11px] flex items-center justify-center shadow-xs">
                            <?= strtoupper(substr($userName, 0, 1)) ?>
                        </div>
                        <div class="hidden sm:block text-left">
                            <span class="font-bold text-zcTxt block leading-none truncate max-w-[110px]"><?= htmlspecialchars($userName) ?></span>
                            <span class="text-[10px] text-zcMut capitalize leading-none"><?= htmlspecialchars($userRole) ?></span>
                        </div>
                    </a>
                    <a href="../logout.php" title="Keluar Akun" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-2 rounded-xl border border-transparent hover:border-rose-200 transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                    </a>
                <?php else: ?>
                    <a href="../login_customer.php?redirect=ecommerce/index.php" class="text-sm font-semibold border border-zcBrd px-4 py-2 rounded-xl bg-white text-zcMut hover:text-zcTxt hover:border-zc transition shadow-xs">
                        Masuk
                    </a>
                    <a href="../register.php" class="text-sm font-bold bg-zcLt text-zc border border-zc/25 px-4 py-2 rounded-xl hover:bg-zc/15 transition shadow-xs hidden sm:inline-block">
                        Daftar
                    </a>
                <?php endif; ?>

                <!-- Cart Button -->
                <a href="../zencare_checkout.php" class="relative inline-flex items-center gap-2 px-4 py-2 bg-zc hover:bg-zcHv text-white text-sm font-bold rounded-xl transition shadow-sm active:scale-95">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.55L23 6H6"/>
                    </svg>
                    <span class="hidden sm:inline">Keranjang</span>
                    <span id="cart-badge" class="px-1.5 py-0.5 text-xs font-black bg-white text-zc rounded-full min-w-[20px] text-center shadow-xs">0</span>
                </a>

            </div>
        </div>

        <div class="md:hidden px-4 pb-3 pt-2 border-t border-slate-100 bg-white">
            <form action="kategori.php" method="GET" class="relative">
                <svg class="w-4 h-4 text-zcMut absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Cari nama produk atau kategori..."
                    class="w-full text-sm pl-9 pr-4 py-2.5 border border-zcBrd rounded-xl bg-slate-50 focus:outline-none focus:border-zc focus:bg-white transition-all">
            </form>
        </div>
    </header>
