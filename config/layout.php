<?php
// File: config/layout.php – ZenCare Medical v3.0 (Final TA — Sitemap Sesuai TA)
// Role: superadmin (Pemilik) | admin (Staff Operasional)
// Branch Selector: DISEMBUNYIKAN — hardcode ke CABANG_UTAMA_ID

function layoutHead(string $title = 'Dashboard') {
    echo '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . htmlspecialchars($title) . ' – ZenCare Medical</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<!-- jQuery & Select2 for Searchable Dropdowns (Offline Local Assets + CDN Fallback) -->
<script src="/inventory_zencare/assets/js/jquery-3.7.1.min.js"></script>
<script>if (typeof jQuery === \'undefined\') { document.write(\'<script src="https://code.jquery.com/jquery-3.7.1.min.js"><\\/script>\'); }</script>
<link href="/inventory_zencare/assets/css/select2.min.css" rel="stylesheet" />
<script src="/inventory_zencare/assets/js/select2.min.js"></script>
<!-- SweetAlert2 (Offline Local Asset + CDN Fallback) -->
<script src="/inventory_zencare/assets/js/sweetalert2.all.min.js"></script>
<script>if (typeof Swal === \'undefined\') { document.write(\'<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\\/script>\'); }</script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: { sans: ["Inter", "sans-serif"] },
        colors: {
          zc:      "#1a75d2",
          zcNavy:  "#0f2d5a",
          zcHv:    "#0f2d5a",
          zcLt:    "#eef6fb",
          zcRed:   "#c0392b",
          zcBg:    "#f8fafc",
          zcCard:  "#ffffff",
          zcBrd:   "#e2e8f0",
          zcTxt:   "#0f2d5a",
          zcMut:   "#64748b",
          zcEm:    "#059669",
        }
      }
    }
  }
</script>
<style>
  ::-webkit-scrollbar{width:5px;height:5px}
  ::-webkit-scrollbar-track{background:transparent}
  ::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:6px}
  body{color:#1e293b;background-color:#f8fafc}
  .sidebar-link{display:flex;align-items:center;gap:10px;padding:8.5px 12px;border-radius:6px;font-size:13px;font-weight:500;color:#475569;transition:all .15s}
  .sidebar-link:hover{background:#f1f5f9;color:#0f2d5a}
  .sidebar-link.active{background:#eef6fb;color:#0f2d5a;font-weight:700;border-left:3px solid #1a75d2;padding-left:9px}
  .sidebar-link.active svg{color:#1a75d2;opacity:1}
  .sidebar-link svg{flex-shrink:0;color:#64748b;opacity:.85}
  .sidebar-section{font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.07em;padding:16px 12px 6px}

  /* ── Global Readability Upgrade (No Microscopic 9px/10px Text) ────────── */
  .text-\[9px\], .text-\[10px\] {
    font-size: 11px !important;
    line-height: 1.35 !important;
  }
  .text-\[11px\] {
    font-size: 11.5px !important;
    line-height: 1.4 !important;
  }
  [style*="font-size:9px"], [style*="font-size: 9px"],
  [style*="font-size:10px"], [style*="font-size: 10px"] {
    font-size: 11px !important;
    line-height: 1.35 !important;
  }
  [style*="font-size:11px"], [style*="font-size: 11px"] {
    font-size: 11.5px !important;
    line-height: 1.4 !important;
  }

  /* ── Responsive Adaptation for Laptop vs PC Resolutions (1280px–1536px) ─ */
  @media (max-width: 1536px) {
    table.min-w-\[900px\], table.min-w-\[920px\], table.min-w-\[950px\],
    table.min-w-\[1000px\], table.min-w-\[1020px\], table.min-w-\[1050px\],
    table.min-w-\[1100px\] {
      min-width: 100% !important;
    }
    table th, table td {
      padding-left: 0.625rem !important;
      padding-right: 0.625rem !important;
    }
  }

  /* ── Harmonize Off-Brand Purple/Indigo AI Accents to ZenCare Logo Palette ── */
  .bg-indigo-50, .bg-purple-50, .bg-violet-50, .bg-indigo-50\/50, .bg-purple-50\/50 {
    background-color: #eef6fb !important;
  }
  .bg-indigo-100, .bg-purple-100, .bg-violet-100 {
    background-color: #dbeafe !important;
  }
  .text-indigo-600, .text-indigo-700, .text-indigo-800,
  .text-purple-600, .text-purple-700, .text-purple-800,
  .text-violet-600, .text-violet-700 {
    color: #0f2d5a !important;
  }
  .border-indigo-100, .border-indigo-200, .border-purple-100, .border-purple-200, .border-violet-200 {
    border-color: #cbe4f5 !important;
  }
  .bg-indigo-600, .bg-purple-600, .bg-violet-600 {
    background-color: #0f2d5a !important;
  }
  .hover\:bg-indigo-700:hover, .hover\:bg-purple-700:hover {
    background-color: #1a75d2 !important;
  }

  /* ── Clean Enterprise Table & Card Refinement ─────────────────────────── */
  table th:not(.text-white):not([class*="text-"]) {
    letter-spacing: 0.04em;
    color: #475569;
  }

  /* ── Modern Minimalist Select2 Theme (Ecommerce-Grade) ────────────────── */
  .select2-container--default .select2-selection--single {
    min-height: 40px !important;
    height: 40px !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 0.75rem !important;
    padding: 6px 12px !important;
    display: flex !important;
    align-items: center !important;
    background-color: #ffffff !important;
    transition: all 0.2s ease !important;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03) !important;
  }
  .select2-container--default.select2-container--open .select2-selection--single,
  .select2-container--default .select2-selection--single:focus {
    border-color: #1a75d2 !important;
    box-shadow: 0 0 0 3px rgba(26, 117, 210, 0.12) !important;
    outline: none !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #1e293b !important;
    font-size: 0.8125rem !important;
    font-weight: 500 !important;
    padding-left: 0 !important;
    padding-right: 20px !important;
    line-height: normal !important;
    flex: 1 1 auto !important;
    min-width: 0 !important;
    width: 100% !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #94a3b8 !important;
    font-size: 0.8125rem !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 100% !important;
    right: 10px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: #94a3b8 transparent transparent transparent !important;
    border-width: 5px 4px 0 4px !important;
  }
  .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
    border-color: transparent transparent #1a75d2 transparent !important;
    border-width: 0 4px 5px 4px !important;
  }
  .select2-dropdown {
    border: 1px solid #e2e8f0 !important;
    border-radius: 0.875rem !important;
    box-shadow: 0 12px 30px -4px rgba(15, 23, 42, 0.12), 0 4px 6px -2px rgba(15, 23, 42, 0.04) !important;
    overflow: hidden !important;
    z-index: 9999 !important;
    margin-top: 4px !important;
    background-color: #ffffff !important;
    padding: 4px !important;
  }
  .select2-search--dropdown {
    padding: 6px !important;
  }
  .select2-search--dropdown .select2-search__field {
    border: 1px solid #e2e8f0 !important;
    border-radius: 0.5rem !important;
    padding: 7px 10px !important;
    font-size: 0.8125rem !important;
    background-color: #f8fafc !important;
    color: #1e293b !important;
    outline: none !important;
    transition: all 0.15s ease !important;
  }
  .select2-search--dropdown .select2-search__field:focus {
    border-color: #1a75d2 !important;
    background-color: #ffffff !important;
    box-shadow: 0 0 0 3px rgba(26, 117, 210, 0.1) !important;
  }
  .select2-results__option {
    font-size: 0.8125rem !important;
    padding: 8px 12px !important;
    border-radius: 0.5rem !important;
    margin-bottom: 2px !important;
    color: #334155 !important;
    transition: all 0.1s ease !important;
  }
  .select2-results__option--highlighted {
    background-color: #eff6ff !important;
    color: #1a75d2 !important;
    font-weight: 600 !important;
  }
  .select2-results__option[aria-selected="true"] {
    background-color: #1a75d2 !important;
    color: #ffffff !important;
    font-weight: 600 !important;
  }
  .select2-results__options {
    max-height: 250px !important;
    overflow-y: auto !important;
  }
  .select2-container--default .select2-results__message {
    color: #94a3b8 !important;
    font-size: 0.75rem !important;
    padding: 12px !important;
    text-align: center !important;
  }
  /* Table context compact select2 */
  table .select2-container--default .select2-selection--single {
    min-height: 34px !important;
    height: 34px !important;
    padding: 4px 8px !important;
    border-radius: 0.5rem !important;
  }
  table .select2-container--default .select2-selection--single .select2-selection__rendered {
    font-size: 0.75rem !important;
    flex: 1 1 auto !important;
    min-width: 0 !important;
    width: 100% !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
  }
  table .select2-container--default .select2-selection--single .select2-selection__arrow {
    right: 6px !important;
  }
</style>';
}

function layoutBodyOpen() {
    echo '</head><body class="bg-zcBg text-zcTxt font-sans antialiased min-h-screen flex">';
}

// ── SVG Icon Helper ──────────────────────────────────────────────────────────
function icon(string $name, string $cls = 'w-4 h-4'): string {
    $icons = [
        'dashboard'    => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        'chart_mgr'    => '<path d="M3 3v18h18"/><path d="M7 16l4-4 4 4 5-5"/>', // Grafik untuk Manajerial
        'pos'          => '<rect x="3" y="3" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4"/><path d="M9 8h1M12 8h3"/>',
        'store'        => '<path d="M3 9l9-6 9 6v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path d="M9 22V12h6v10"/>',
        'orders'       => '<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/>',
        'receive'      => '<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/>', // Penerimaan
        'po'           => '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13h8M8 17h4"/><path d="M12 10v6M9 13h6"/>', // PO
        'swap'         => '<path d="M7 16V4m0 0L3 8m4-4 4 4M17 8v12m0 0 4-4m-4 4-4-4"/>',
        'kartu'        => '<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12l2 2 4-4"/>',
        'pill'         => '<path d="m10.5 20.5 10-10a4.95 4.95 0 10-7-7l-10 10a4.95 4.95 0 107 7z"/><line x1="8.5" y1="8.5" x2="15.5" y2="15.5"/>',
        'uom'          => '<path d="M7 4v16M17 4v16M3 8h4m10 0h4M3 16h4m10 0h4M7 12h10"/>',
        'users'        => '<path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>',
        'report_sale'  => '<path d="M3 3v18h18"/><path d="M18 17V9M13 17V5M8 17v-3"/>',
        'report_stock' => '<path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2z"/><path d="M7 7h.01"/>',
        'report_med'   => '<rect x="3" y="7" width="18" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><path d="M12 11v6M9 14h6"/>',
        'report_fefo'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M12 8v4"/><path d="M12 16h.01"/>',
        'settings'     => '<path d="M12.22 2h-.44a2 2 0 00-2 2v.18a2 2 0 01-1 1.73l-.43.25a2 2 0 01-2 0l-.15-.08a2 2 0 00-2.73.73l-.22.38a2 2 0 00.73 2.73l.15.1a2 2 0 011 1.72v.51a2 2 0 01-1 1.74l-.15.09a2 2 0 00-.73 2.73l.22.38a2 2 0 002.73.73l.15-.08a2 2 0 012 0l.43.25a2 2 0 011 1.73V20a2 2 0 002 2h.44a2 2 0 002-2v-.18a2 2 0 011-1.73l.43-.25a2 2 0 012 0l.15.08a2 2 0 002.73-.73l.22-.39a2 2 0 00-.73-2.73l-.15-.08a2 2 0 01-1-1.74v-.5a2 2 0 011-1.74l.15-.09a2 2 0 00.73-2.73l-.22-.38a2 2 0 00-2.73-.73l-.15.08a2 2 0 01-2 0l-.43-.25a2 2 0 01-1-1.73V4a2 2 0 00-2-2z"/><circle cx="12" cy="12" r="3"/>',
        'logout'       => '<path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>',
        'truck'        => '<path d="M14 18V6a2 2 0 00-2-2H4a2 2 0 00-2 2v11a1 1 0 001 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 001-1v-3.65a1 1 0 00-.22-.624l-3.48-4.35A1 1 0 0017.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>',
        'opname'       => '<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 14l2 2 4-4"/><path d="M9 10h6"/>',
    ];
    $path = $icons[$name] ?? $icons['settings'];
    return '<svg class="' . $cls . ' shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
}

// ── Sidebar Navigation (Sesuai Sitemap Final TA) ────────────────────────────
function layoutSidebar(string $activeMenu = 'dashboard') {
    $role        = $_SESSION['role'] ?? '';
    $isSuperadmin = ($role === 'superadmin');
    $isStaff     = in_array($role, ['superadmin', 'admin']); // Admin atau Superadmin
    $nama        = htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User');
    $initial     = strtoupper(substr($nama, 0, 1));
    $roleLabel   = match($role) {
        'superadmin' => 'Superadmin',
        'admin'      => 'Admin',
        default      => 'Tamu'
    };

    $link = function(string $href, string $iconName, string $label, string $key) use ($activeMenu) {
        $active = ($activeMenu === $key || ($key === 'mutasi' && in_array($activeMenu, ['mutasi', 'mutasi_stok']))) ? ' active' : '';
        echo '<a href="' . $href . '" class="sidebar-link' . $active . '">';
        echo icon($iconName);
        echo '<span>' . $label . '</span></a>';
    };

    echo '<aside id="app-sidebar" class="w-[216px] xl:w-[236px] bg-white border-r border-zcBrd flex flex-col shrink-0 min-h-screen sticky top-0 hidden md:flex transition-all duration-200 z-40">';

    // Brand logo (Harmonized with ZenCare Logo colors: Navy #0f2d5a + Medical Crimson #c0392b)
    echo '<div class="h-16 flex items-center px-4 border-b border-zcBrd gap-3">
      <div class="w-10 h-10 rounded-full bg-white border border-zcBrd flex items-center justify-center shrink-0 overflow-hidden">
        <img src="/inventory_zencare/gambar/Logo.png" alt="ZenCare Logo" class="w-full h-full object-contain rounded-full">
      </div>
      <div>
        <div class="text-[14px] font-extrabold leading-tight tracking-tight">
          <span class="text-[#0f2d5a]">ZenCare</span> <span class="text-[#c0392b]">Medical</span>
        </div>
        <span class="text-[11px] text-slate-500 font-medium block">Sistem Inventori &amp; POS</span>
      </div>
    </div>';

    echo '<nav class="flex-1 px-2 py-3 overflow-y-auto">';

    // ── UTAMA ──────────────────────────────────────────────────────────────
    echo '<p class="sidebar-section">Utama</p>';
    $link('/inventory_zencare/index.php', 'dashboard', 'Dashboard', 'dashboard');

    // ==============================================================================
    // MENU UTAMA 
    // ==============================================================================
    if ($isStaff) {
        echo '<p class="sidebar-section">Transaksi</p>';
        if (in_array($role, ['superadmin', 'kasir'])) {
            $link('/inventory_zencare/pos/pos.php', 'pos', 'Kasir (POS)', 'pos');
        }
        $link('/inventory_zencare/admin/pesanan.php', 'orders', 'Transaksi', 'pesanan');
        if ($role === 'kasir') {
            $link('/inventory_zencare/inventori/stok_opname.php', 'opname', 'Blind Count Opname', 'stok_opname');
        }

        // ── MASTER DATA ───────────────────────────────────────────────────
        if (in_array($role, ['superadmin', 'admin'])) {
            echo '<p class="sidebar-section">Master Data</p>';
            $link('/inventory_zencare/admin/master_produk.php', 'pill', 'Produk', 'master_produk');
            $link('/inventory_zencare/admin/master_uom.php', 'uom', 'Satuan & UOM', 'master_uom');
            $link('/inventory_zencare/admin/master_supplier.php', 'truck', 'Supplier', 'master_supplier');

            // ── LOGISTIK & INVENTORI ──────────────────────────────────────────
            echo '<p class="sidebar-section">Logistik & Inventori</p>';
            $link('/inventory_zencare/inventori/tambah_stok.php', 'receive', 'Penerimaan Barang', 'tambah_stok');
            $link('/inventory_zencare/inventori/proses_mutasi.php', 'swap', 'Mutasi Stok Manual', 'mutasi');
            $link('/inventory_zencare/inventori/konversi_uom.php', 'uom', 'Konversi (UOM)', 'konversi_uom');
            $link('/inventory_zencare/inventori/histori_konversi.php', 'report_sale', 'Histori Konversi', 'histori_konversi');
            $link('/inventory_zencare/inventori/stok_opname.php', 'opname', 'Stok Opname', 'stok_opname');
            $link('/inventory_zencare/laporan/kartu_stok.php', 'kartu', 'Kartu Stok', 'laporan_kartu_stok');
            if ($role === 'admin') {
                $link('/inventory_zencare/inventori/logistik_medis.php', 'report_med', 'Logistik Medis', 'logistik_medis_admin');
            }
        }
    }

    // ── LAPORAN (KHUSUS SUPERADMIN / OWNER) ───────────────────────────
    if ($isSuperadmin) {
        echo '<p class="sidebar-section">Laporan</p>';
        $link('/inventory_zencare/laporan/penjualan.php', 'report_sale', 'Lap. Penjualan Omnichannel', 'laporan_penjualan');
        $link('/inventory_zencare/laporan/ketersediaan_stok.php', 'report_stock', 'Lap. Persediaan', 'laporan_ketersediaan');
        $link('/inventory_zencare/laporan/logistik_medis.php', 'report_med', 'Lap. Logistik Medis', 'laporan_logistik');
        $link('/inventory_zencare/laporan/penerimaan_barang.php', 'receive', 'Lap. Penerimaan Barang', 'laporan_penerimaan');
        $link('/inventory_zencare/laporan/mutasi_konversi.php', 'swap', 'Lap. Mutasi & Konversi', 'laporan_mutasi_konversi');
        $link('/inventory_zencare/laporan/anomali_fefo.php', 'report_fefo', 'Lap. Anomali FEFO', 'laporan_anomali_fefo');
    }

    if ($isSuperadmin) {

        // ── MANAJEMEN & SISTEM ────────────────────────────────────────────
        echo '<p class="sidebar-section">Manajemen & Sistem</p>';
        $link('/inventory_zencare/admin/master_user.php', 'users', 'Manajemen Pengguna', 'master_user');
        $link('/inventory_zencare/ecommerce/index.php', 'store', 'Buka Toko E-Commerce', 'store');
        $link('/inventory_zencare/admin/pengaturan_api.php', 'settings', 'Pengaturan API', 'pengaturan');
        $link('/inventory_zencare/admin/pengaturan_web.php', 'settings', 'Pengaturan Web', 'pengaturan_web');
    }

    echo '</nav>';

    // User card
    echo '<div class="px-3 py-3 border-t border-zcBrd">
      <div class="flex items-center gap-2.5 p-2.5 rounded-lg bg-[#f8fafc] border border-zcBrd mb-2">
        <div class="w-8 h-8 rounded-full bg-[#0f2d5a] text-white font-bold text-xs flex items-center justify-center shrink-0">' . $initial . '</div>
        <div class="min-w-0 flex-1">
          <span class="text-xs font-bold text-[#0f2d5a] truncate block leading-snug">' . $nama . '</span>
          <span class="text-[11px] text-slate-500 font-medium block">' . $roleLabel . '</span>
        </div>
      </div>
      <a href="/inventory_zencare/logout.php" class="sidebar-link text-[#c0392b] hover:!bg-rose-50 hover:!text-rose-700 justify-center gap-2 py-2 text-xs font-semibold">'
      . icon('logout', 'w-4 h-4') . ' <span>Keluar Sistem</span></a>
    </div></aside>';
}

// ── Page Header (Branch selector DISEMBUNYIKAN per TA) ──────────────────────
function layoutHeader(string $title, string $subtitle = '', bool $showBranchSelect = false) {
    // Branch selector: DISEMBUNYIKAN sesuai batasan TA (single-branch view)
    // Jika perlu aktifkan kembali, ubah parameter $showBranchSelect = true
    global $pdo;
    $nama      = htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User');
    $initial   = strtoupper(substr($nama, 0, 1));
    $role      = $_SESSION['role'] ?? '';
    $roleLabel = match($role) {
        'superadmin' => 'Superadmin',
        'admin'      => 'Admin',
        default      => 'Tamu'
    };

    echo '<div class="flex-1 flex flex-col min-w-0">
    <header class="h-16 bg-white border-b border-zcBrd px-5 flex items-center justify-between sticky top-0 z-30 shrink-0">
      <div>
        <h1 class="text-base font-bold text-[#0f2d5a] leading-tight">' . htmlspecialchars($title) . '</h1>';
    if ($subtitle) echo '<p class="text-xs text-slate-500 mt-0.5">' . htmlspecialchars($subtitle) . '</p>';
    echo '  </div>
      <div class="flex items-center gap-3 shrink-0">';

    /* === BRANCH SELECTOR DISEMBUNYIKAN (single-branch view per TA) ===
    // Kode ini disembunyikan, bukan dihapus, agar bisa diaktifkan kembali
    if ($showBranchSelect && isset($pdo)) {
        // ... kode branch selector ...
    }
    === END BRANCH SELECTOR === */

    // Tampilkan hanya info cabang statis (tanpa dropdown ganti cabang)
    $cabangNama = 'Pusat (Muharto)';
    echo '<div class="hidden sm:flex items-center gap-1.5 text-xs bg-[#eef6fb] border border-[#cbe4f5] text-[#0f2d5a] rounded-md px-3 py-1.5 font-semibold">
        <svg class="w-3.5 h-3.5 text-[#1a75d2]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-6 9 6v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
        ' . htmlspecialchars($cabangNama) . '
      </div>';

    echo '<div class="flex items-center gap-2.5 border border-zcBrd px-3 py-1.5 rounded-md bg-white">
          <div class="w-7 h-7 rounded-full bg-[#0f2d5a] text-white font-bold text-xs flex items-center justify-center">' . $initial . '</div>
          <div>
            <span class="text-xs font-bold text-[#0f2d5a] block leading-tight">' . $nama . '</span>
            <span class="text-[11px] text-slate-500 block leading-tight">' . $roleLabel . '</span>
          </div>
        </div>
      </div>
    </header>';

    require_once __DIR__ . '/opname_helper.php';
    $sesiOpnameGlobal = (isset($pdo) && function_exists('cekSesiOpnameAktif')) ? cekSesiOpnameAktif($pdo) : null;
    $currentPage = basename($_SERVER['PHP_SELF'] ?? '');

    if ($sesiOpnameGlobal && $currentPage !== 'stok_opname.php') {
        echo '<div class="bg-amber-500 text-white px-5 py-2.5 text-xs font-semibold flex flex-col sm:flex-row sm:items-center justify-between gap-2 shadow-xs sticky top-14 z-20 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-6 h-6 rounded-md bg-amber-600/60 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <span><strong>SESI STOK OPNAME AKTIF (' . htmlspecialchars($sesiOpnameGlobal['kode_sesi']) . '):</strong> Transaksi kasir POS, checkout e-commerce, penerimaan logistik, dan mutasi inventori sedang dibekukan sementara.</span>
            </div>
            <a href="/inventory_zencare/inventori/stok_opname.php" class="bg-white/20 hover:bg-white/30 text-white px-3 py-1 rounded-lg text-[11px] font-bold transition flex items-center justify-center gap-1 self-start sm:self-auto shrink-0">
                <span>Buka Stok Opname</span>
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>';
    }

    echo '<main class="p-3 sm:p-4 xl:p-6 flex-1 min-w-0 overflow-x-hidden">';
}

function layoutEnd() {
    echo '</main></div>
    <script>
      $(document).ready(function() {
          $("select.select2:not(.select2-hidden-accessible)").each(function() {
              $(this).select2({ width: "100%" });
              $(this).on("select2:select", function(e) {
                  this.dispatchEvent(new Event("change", { bubbles: true }));
              });
          });
      });
    </script>
    </body></html>';
}

function layoutFooter() {
    layoutEnd();
}
?>
