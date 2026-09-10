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
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: { sans: ["Inter", "sans-serif"] },
        colors: {
          zc:      "#1a75d2",
          zcHv:    "#1562b3",
          zcLt:    "#e8f2ff",
          zcRed:   "#c0392b",
          zcBg:    "#f5f7fa",
          zcCard:  "#ffffff",
          zcBrd:   "#e4e9f0",
          zcTxt:   "#1e293b",
          zcMut:   "#64748b",
          zcEm:    "#059669",
        }
      }
    }
  }
</script>
<style>
  ::-webkit-scrollbar{width:4px;height:4px}
  ::-webkit-scrollbar-track{background:transparent}
  ::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:8px}
  body{animation:fadeIn .15s ease-out}
  @keyframes fadeIn{from{opacity:.8;transform:translateY(3px)}to{opacity:1;transform:none}}
  .sidebar-link{display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:10px;font-size:12.5px;font-weight:500;color:#475569;transition:all .15s}
  .sidebar-link:hover{background:#f1f5f9;color:#1e293b}
  .sidebar-link.active{background:#eff6ff;color:#1a75d2;font-weight:600}
  .sidebar-link.active svg{color:#1a75d2}
  .sidebar-link svg{flex-shrink:0;opacity:.65}
  .sidebar-link.active svg{opacity:1}
  .sidebar-link.active{border-left:3px solid #1a75d2;padding-left:9px}
  .sidebar-section{font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;padding:14px 12px 6px}
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
        'report_med'   => '<path d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8zM6 1v3M10 1v3M14 1v3"/>',
        'settings'     => '<path d="M12.22 2h-.44a2 2 0 00-2 2v.18a2 2 0 01-1 1.73l-.43.25a2 2 0 01-2 0l-.15-.08a2 2 0 00-2.73.73l-.22.38a2 2 0 00.73 2.73l.15.1a2 2 0 011 1.72v.51a2 2 0 01-1 1.74l-.15.09a2 2 0 00-.73 2.73l.22.38a2 2 0 002.73.73l.15-.08a2 2 0 012 0l.43.25a2 2 0 011 1.73V20a2 2 0 002 2h.44a2 2 0 002-2v-.18a2 2 0 011-1.73l.43-.25a2 2 0 012 0l.15.08a2 2 0 002.73-.73l.22-.39a2 2 0 00-.73-2.73l-.15-.08a2 2 0 01-1-1.74v-.5a2 2 0 011-1.74l.15-.09a2 2 0 00.73-2.73l-.22-.38a2 2 0 00-2.73-.73l-.15.08a2 2 0 01-2 0l-.43-.25a2 2 0 01-1-1.73V4a2 2 0 00-2-2z"/><circle cx="12" cy="12" r="3"/>',
        'logout'       => '<path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>',
        'truck'        => '<path d="M14 18V6a2 2 0 00-2-2H4a2 2 0 00-2 2v11a1 1 0 001 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 001-1v-3.65a1 1 0 00-.22-.624l-3.48-4.35A1 1 0 0017.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>',
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
        $active = $activeMenu === $key ? ' active' : '';
        echo '<a href="' . $href . '" class="sidebar-link' . $active . '">';
        echo icon($iconName);
        echo '<span>' . $label . '</span></a>';
    };

    echo '<aside class="w-[230px] bg-white border-r border-zcBrd flex flex-col shrink-0 min-h-screen sticky top-0 hidden md:flex">';

    // Brand logo
    echo '<div class="h-14 flex items-center px-4 border-b border-zcBrd gap-2.5">
      <div class="w-8 h-8 rounded-xl bg-zc flex items-center justify-center shrink-0 shadow-sm shadow-blue-500/30">
        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
      </div>
      <div>
        <span class="text-[13px] font-black text-zcTxt leading-tight block">ZenCare Medical</span>
        <span class="text-[9px] text-zcMut font-medium block tracking-wide uppercase">Inventory System v3</span>
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
        $link('/inventory_zencare/pos/pos.php', 'pos', 'Kasir (POS)', 'pos');
        $link('/inventory_zencare/admin/pesanan.php', 'orders', 'Pesanan Daring', 'pesanan');

        // ── MASTER DATA ───────────────────────────────────────────────────
        echo '<p class="sidebar-section">Master Data</p>';
        $link('/inventory_zencare/admin/master_produk.php', 'pill', 'Produk', 'master_produk');
        $link('/inventory_zencare/admin/master_uom.php', 'uom', 'Satuan & UOM', 'master_uom');
        $link('/inventory_zencare/admin/master_supplier.php', 'truck', 'Supplier', 'master_supplier');

        // ── LOGISTIK & INVENTORI ──────────────────────────────────────────
        echo '<p class="sidebar-section">Logistik & Inventori</p>';
        $link('/inventory_zencare/inventori/tambah_stok.php', 'receive', 'Penerimaan Barang', 'tambah_stok');
        $link('/inventory_zencare/inventori/proses_mutasi.php', 'swap', 'Mutasi Stok', 'mutasi');
        $link('/inventory_zencare/laporan/kartu_stok.php', 'kartu', 'Kartu Stok', 'laporan_kartu_stok');
    }

    // ── LAPORAN — SUPERADMIN ONLY ────────────────────────────────────────
    if ($isSuperadmin) {
        echo '<p class="sidebar-section">Laporan</p>';
        $link('/inventory_zencare/laporan/penjualan.php', 'report_sale', 'Lap. Penjualan Barang', 'laporan_penjualan');
        $link('/inventory_zencare/laporan/ketersediaan_stok.php', 'report_stock', 'Lap. Persediaan', 'laporan_ketersediaan');
        $link('/inventory_zencare/laporan/logistik_medis.php', 'report_med', 'Lap. Logistik Medis', 'laporan_logistik');

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
      <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-zcBrd mb-2">
        <div class="w-8 h-8 rounded-full bg-zc text-white font-bold text-xs flex items-center justify-center shrink-0">' . $initial . '</div>
        <div class="min-w-0 flex-1">
          <span class="text-xs font-semibold text-zcTxt truncate block leading-snug">' . $nama . '</span>
          <span class="text-[9px] text-zcMut uppercase tracking-wide">' . $roleLabel . '</span>
        </div>
      </div>
      <a href="/inventory_zencare/logout.php" class="sidebar-link text-rose-500 hover:!bg-rose-50 hover:!text-rose-600 justify-center gap-2 py-2 text-xs font-semibold">'
      . icon('logout', 'w-3.5 h-3.5') . ' <span>Keluar Sistem</span></a>
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
    <header class="h-14 bg-white border-b border-zcBrd px-5 flex items-center justify-between sticky top-0 z-30 shrink-0">
      <div>
        <h1 class="text-[15px] font-semibold text-zcTxt leading-tight">' . htmlspecialchars($title) . '</h1>';
    if ($subtitle) echo '<p class="text-[11px] text-zcMut mt-0.5">' . htmlspecialchars($subtitle) . '</p>';
    echo '  </div>
      <div class="flex items-center gap-3">';

    /* === BRANCH SELECTOR DISEMBUNYIKAN (single-branch view per TA) ===
    // Kode ini disembunyikan, bukan dihapus, agar bisa diaktifkan kembali
    if ($showBranchSelect && isset($pdo)) {
        // ... kode branch selector ...
    }
    === END BRANCH SELECTOR === */

    // Tampilkan hanya info cabang statis (tanpa dropdown ganti cabang)
    $cabangNama = 'Pusat (Muharto)';
    echo '<div class="hidden sm:flex items-center gap-1.5 text-[10px] bg-blue-50 border border-blue-100 text-[#1a75d2] rounded-lg px-2.5 py-1.5 font-semibold">
        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-6 9 6v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
        ' . htmlspecialchars($cabangNama) . '
      </div>';

    echo '<div class="flex items-center gap-2 border border-zcBrd px-3 py-1.5 rounded-full bg-white">
          <div class="w-6 h-6 rounded-full bg-zc text-white font-semibold text-[11px] flex items-center justify-center">' . $initial . '</div>
          <div>
            <span class="text-[11px] font-semibold text-zcTxt block leading-tight">' . $nama . '</span>
            <span class="text-[9px] text-zcMut uppercase tracking-wide">' . $roleLabel . '</span>
          </div>
        </div>
      </div>
    </header>
    <main class="p-6 flex-1 overflow-x-hidden">';
}

function layoutEnd() {
    echo '</main></div></body></html>';
}

function layoutFooter() {
    layoutEnd();
}
?>
