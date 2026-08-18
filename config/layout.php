<?php
// File: config/layout.php – ZenCare Medical Brand System (v3 – Fresh Blue/White)
// Usage: layoutHead($title), layoutBodyOpen(), layoutSidebar($activeMenu), layoutHeader($title, $subtitle), layoutEnd()

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
  .sidebar-link{display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:10px;font-size:13px;font-weight:500;color:#475569;transition:all .15s}
  .sidebar-link:hover{background:#f1f5f9;color:#1e293b}
  .sidebar-link.active{background:#1a75d2;color:#fff;font-weight:600}
  .sidebar-link.active svg{stroke:#fff}
</style>';
}

function layoutBodyOpen() {
    echo '</head><body class="bg-zcBg text-zcTxt font-sans antialiased min-h-screen flex">';
}

// ── SVG Icon Helper ──────────────────────────────────────────────
function icon(string $name, string $cls = 'w-[18px] h-[18px] stroke-slate-500 shrink-0'): string {
    $icons = [
        'dashboard' => '<polyline points="3 9 12 2 21 9"/><polyline points="9 22 9 12 15 12 15 22"/><path d="M3 9v13h18V9"/>',
        'pos'       => '<rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>',
        'store'     => '<path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>',
        'swap'      => '<path d="M7 16V4m0 0L3 8m4-4l4 4"/><path d="M17 8v12m0 0l4-4m-4 4l-4-4"/>',
        'download'  => '<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
        'shield'    => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'pill'      => '<path d="m10.5 20.5 10-10a4.95 4.95 0 10-7-7l-10 10a4.95 4.95 0 107 7z"/><line x1="8.5" y1="8.5" x2="15.5" y2="15.5"/>',
        'users'     => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>',
        'truck'     => '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
        'building'  => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>',
        'logout'    => '<path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'db'        => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>',
        'bolt'      => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
        'chart'     => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
        'orders'    => '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
    ];
    $path = $icons[$name] ?? $icons['settings'];
    return '<svg class="' . $cls . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
}

function layoutSidebar(string $activeMenu = 'dashboard') {
    $role      = $_SESSION['role'] ?? '';
    $isAdmin   = ($role === 'super_admin');
    $nama      = htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User');
    $initial   = strtoupper(substr($nama, 0, 1));
    $roleLabel = match($role) { 'super_admin' => 'Super Admin', 'kasir' => 'Kasir', default => 'Pelanggan' };

    $link = function(string $href, string $iconName, string $label, string $key) use ($activeMenu) {
        $active = $activeMenu === $key ? ' active' : '';
        echo '<a href="' . $href . '" class="sidebar-link' . $active . '">';
        echo icon($iconName);
        echo '<span>' . $label . '</span></a>';
    };

    echo '<aside class="w-60 bg-white border-r border-zcBrd flex flex-col shrink-0 min-h-screen sticky top-0 hidden md:flex">';

    // Brand
    echo '<div class="h-16 flex items-center px-5 border-b border-zcBrd gap-3">
      <div class="w-8 h-8 rounded-lg bg-zc flex items-center justify-center shrink-0">
        <svg viewBox="0 0 24 24" class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
      </div>
      <div>
        <span class="text-sm font-bold text-zcTxt leading-tight block">ZenCare</span>
        <span class="text-[10px] text-zcMut font-medium block">Medical Omnichannel</span>
      </div>
    </div>';

    echo '<nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">';

    echo '<p class="text-[10px] font-semibold text-zcMut uppercase tracking-widest px-3 pt-2 pb-1">Utama</p>';
    $link('/inventory_zencare/index.php', 'dashboard', 'Dashboard', 'dashboard');

    if ($role === 'kasir' || $isAdmin) {
        echo '<p class="text-[10px] font-semibold text-zcMut uppercase tracking-widest px-3 pt-4 pb-1">Transaksi</p>';
        $link('/inventory_zencare/admin/pesanan.php', 'orders', 'Pesanan Online', 'pesanan');
        $link('/inventory_zencare/pos/pos.php', 'pos', 'Terminal POS Kasir', 'pos');
        $link('/inventory_zencare/zencare_store.php', 'store', 'Toko E-Commerce', 'store');
        echo '<p class="text-[10px] font-semibold text-zcMut uppercase tracking-widest px-3 pt-4 pb-1">Inventori</p>';
        $link('/inventory_zencare/inventori/proses_mutasi.php', 'swap', 'Mutasi Stok', 'mutasi');
        $link('/inventory_zencare/inventori/import_pengadaan.php', 'download', 'Import Pengadaan', 'import');
        $link('/inventory_zencare/inventori/karantina.php', 'shield', 'Gudang Karantina', 'karantina');
    }

    if ($isAdmin) {
        echo '<p class="text-[10px] font-semibold text-zcMut uppercase tracking-widest px-3 pt-4 pb-1">Master Data</p>';
        $link('/inventory_zencare/admin/master_produk.php', 'pill', 'Produk & Variasi', 'master_produk');
        $link('/inventory_zencare/admin/master_user.php', 'users', 'Manajemen User', 'master_user');
        $link('/inventory_zencare/admin/master_supplier.php', 'truck', 'Supplier', 'master_supplier');
        $link('/inventory_zencare/admin/master_cabang.php', 'building', 'Cabang', 'master_cabang');
        echo '<p class="text-[10px] font-semibold text-zcMut uppercase tracking-widest px-3 pt-4 pb-1">Sistem</p>';
        $link('/inventory_zencare/admin/pengaturan_api.php', 'settings', 'Pengaturan API', 'pengaturan');
    }

    echo '</nav>';

    // User card
    echo '<div class="p-3 border-t border-zcBrd">
      <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-zcBrd mb-2">
        <div class="w-8 h-8 rounded-full bg-zc text-white font-semibold text-sm flex items-center justify-center shrink-0">' . $initial . '</div>
        <div class="min-w-0 flex-1">
          <span class="text-xs font-semibold text-zcTxt truncate block">' . $nama . '</span>
          <span class="text-[10px] text-zcMut">' . $roleLabel . '</span>
        </div>
      </div>
      <a href="/inventory_zencare/logout.php" class="sidebar-link text-rose-500 hover:!bg-rose-50 hover:!text-rose-600 justify-center gap-2 py-2 text-xs">'
      . icon('logout', 'w-4 h-4 stroke-current') . ' Keluar Sistem</a>
    </div></aside>';
}

function layoutHeader(string $title, string $subtitle = '', bool $showBranchSelect = true) {
    global $pdo;
    $nama    = htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User');
    $initial = strtoupper(substr($nama, 0, 1));
    $role    = match($_SESSION['role'] ?? '') { 'super_admin' => 'Super Admin', 'kasir' => 'Kasir', default => 'Pelanggan' };

    echo '<div class="flex-1 flex flex-col min-w-0">
    <header class="h-16 bg-white border-b border-zcBrd px-6 flex items-center justify-between sticky top-0 z-30 shrink-0">
      <div>
        <h1 class="text-base font-semibold text-zcTxt leading-tight">' . htmlspecialchars($title) . '</h1>';
    if ($subtitle) echo '<p class="text-xs text-zcMut mt-0.5">' . htmlspecialchars($subtitle) . '</p>';
    echo '    </div>
      <div class="flex items-center gap-3">';

    if ($showBranchSelect && isset($pdo)) {
        $activeCabang = $_SESSION['id_cabang'] ?? 1;
        try {
            $cabangList = $pdo->query("SELECT id, nama FROM cabang WHERE is_active = 1 ORDER BY id ASC")->fetchAll();
            echo '<form method="GET" class="flex items-center gap-1.5">
              <label class="text-xs text-zcMut font-medium">Cabang</label>
              <select name="cabang" onchange="this.form.submit()" class="text-xs font-medium border border-zcBrd rounded-lg px-2.5 py-1.5 bg-white text-zcTxt focus:outline-none focus:border-zc">';
            foreach ($cabangList as $c) {
                $sel = ($activeCabang == $c['id']) ? 'selected' : '';
                echo "<option value=\"{$c['id']}\" {$sel}>" . htmlspecialchars($c['nama']) . "</option>";
            }
            echo '</select></form>';
        } catch (Exception $e) {}
    }

    echo '<div class="flex items-center gap-2 border border-zcBrd px-3 py-1.5 rounded-full bg-white">
          <div class="w-7 h-7 rounded-full bg-zc text-white font-semibold text-xs flex items-center justify-center">' . $initial . '</div>
          <div>
            <span class="text-xs font-semibold text-zcTxt block leading-tight">' . $nama . '</span>
            <span class="text-[10px] text-zcMut">' . $role . '</span>
          </div>
        </div>
      </div>
    </header>
    <main class="p-6 flex-1 overflow-x-hidden">';
}

function layoutEnd() {
    echo '</main></div></body></html>';
}
?>
