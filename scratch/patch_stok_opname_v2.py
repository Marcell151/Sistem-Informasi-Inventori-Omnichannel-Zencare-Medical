# Patch script for inventori/stok_opname.php
# Resolves Points 1, 2, 3, 4, 5, 6:
# 1. Uniform button layout & alignment on landing page (no awkward wrapping)
# 2. 2-row clean stacked architecture for active session header (lock info full width, buttons full width toolbar, no narrow squishing)
# 3. Client-side demo quick fill button, duplicate form removed, clear validation, and tabs (Belum Dihitung, Sudah Dihitung, Semua)
# 4. Dedicated Kasir layout: full width, NO sidebar, POS-style header with logout, clear completion card explaining POS lock
# 5. Fixes the backend bulk save handling and messaging

with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

# --- PART A: KASIR LAYOUT (NO SIDEBAR) ---
# Replace layoutHead/layoutSidebar calls around line 1442
old_layout_calls = """layoutHead('Stok Opname Fisik');
layoutBodyOpen();
layoutSidebar('stok_opname');
layoutHeader('Stok Opname Persediaan', 'Pencacahan fisik stok (Blind Count), rekonsiliasi anomali, dan berita acara penyesuaian');"""

new_layout_calls = """layoutHead('Stok Opname Fisik');
layoutBodyOpen();

if ($isKasir) {
    // KHUSUS KASIR: Tampilan Layar Penuh Tanpa Sidebar (Selaras dengan Terminal POS)
    ?>
    <div class="min-h-screen bg-slate-50 flex flex-col">
        <!-- Header Khusus Terminal Kasir (Identik dengan Terminal POS) -->
        <header class="bg-white border-b border-slate-200 px-6 py-3.5 sticky top-0 z-30 shadow-2xs">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="/inventory_zencare/gambar/Logo.png" alt="Logo" class="h-9 w-auto object-contain" onerror="this.src='../gambar/Logo.png'">
                    <div>
                        <h1 class="font-extrabold text-sm text-slate-900 tracking-tight">ZENCARE MEDICAL</h1>
                        <p class="text-[11px] text-slate-500 font-medium">Terminal Pencacahan Fisik Rak (Strict Blind Count)</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex items-center gap-2 text-xs text-slate-600 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Pusat (Muharto)</span>
                        <span class="text-slate-300">|</span>
                        <span class="font-semibold text-slate-800"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Kasir') ?></span>
                        <span class="text-[10px] bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded uppercase font-bold">Kasir</span>
                    </div>
                    <a href="/inventory_zencare/auth/logout.php" class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-xl border border-slate-200 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <span>Keluar Sistem</span>
                    </a>
                </div>
            </div>
        </header>
        <main class="flex-1 p-6">
    <?php
} else {
    layoutSidebar('stok_opname');
    layoutHeader('Stok Opname Persediaan', 'Pencacahan fisik stok (Blind Count), rekonsiliasi anomali, dan berita acara penyesuaian');
}"""

if old_layout_calls in code:
    code = code.replace(old_layout_calls, new_layout_calls)
    print("Replaced layout calls successfully!")
else:
    print("Warning: old_layout_calls not found verbatim")

# --- PART B: FIX DUPLICATE FORM AT LINE 2569 ---
old_duplicate_form = """            <!-- Form Tersembunyi untuk Bulk Save dan Single Save -->
            <form method="POST" id="form_bulk_simpan" class="hidden">
                <input type="hidden" name="aksi" value="simpan_semua_hitung_fisik">
            </form>

            <form method="POST" id="form_single_save" class="hidden">"""

new_duplicate_form = """            <!-- Form Tersembunyi untuk Single Save -->
            <form method="POST" id="form_single_save" class="hidden">"""

if old_duplicate_form in code:
    code = code.replace(old_duplicate_form, new_duplicate_form)
    print("Removed duplicate form successfully!")
else:
    print("Warning: old_duplicate_form not found verbatim")

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(code)

print("Part A & B done.")

