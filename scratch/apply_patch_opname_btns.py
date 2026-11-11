import re

with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add reset_database_demo POST handler
post_old = """    // --- 1. MULAI SESI STOK OPNAME (Khusus Superadmin) ---
    if ($aksi === 'mulai_sesi') {"""

post_new = """    // --- RESET DATABASE KE BASELINE AWAL (Khusus Superadmin / Pengujian Demo) ---
    if ($aksi === 'reset_database_demo') {
        if (!$isSuperadmin) {
            $msg = "Akses ditolak! Hanya Superadmin yang berwenang mereset database inventori.";
            $msgType = 'error';
        } else {
            try {
                require_once __DIR__ . '/../scratch/restore_baseline.php';
                restoreBaselineInventory($pdo);
                cekSesiOpnameAktif($pdo, true);
                $msg = "Database inventori berhasil di-reset ke kondisi bersih awal (Baseline Clean State)! Seluruh sesi uji coba dan mutasi sementara telah dibersihkan.";
                $msgType = 'success';
            } catch (Exception $e) {
                $msg = "Gagal mereset database: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }

    // --- 1. MULAI SESI STOK OPNAME (Khusus Superadmin) ---
    elseif ($aksi === 'mulai_sesi') {"""

assert post_old in content, "post_old not found!"
content = content.replace(post_old, post_new, 1)

# 2. Add buttons on Landing Page Header
landing_old = """                <?php if ($isSuperadmin): ?>
                    <button onclick="document.getElementById('modal_mulai_sesi').classList.remove('hidden')" class="px-5 py-2.5 rounded-xl bg-zc hover:bg-zcHv text-white text-xs font-bold transition shadow-md shadow-zc/20 flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                        <span>Mulai Sesi Stok Opname Baru (Bekukan Transaksi)</span>
                    </button>
                <?php else: ?>"""

landing_new = """                <div class="flex items-center gap-2.5 flex-wrap">
                    <!-- Tombol Panduan & Dokumentasi Pengujian Bab 4 -->
                    <a href="dokumentasi_testing.php" target="_blank" class="px-4 py-2.5 rounded-xl border border-zc text-zc hover:bg-zc hover:text-white text-xs font-bold transition flex items-center justify-center gap-2 shadow-2xs">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                        <span>Dokumentasi Uji Coba (Bab 4)</span>
                    </a>

                    <?php if ($isSuperadmin): ?>
                        <!-- Tombol Reset Database Demo -->
                        <form method="POST" onsubmit="return confirm('PERINGATAN AUDIT: Apakah Anda yakin ingin mereset database inventori ke kondisi awal yang bersih? Seluruh data sesi uji coba dan mutasi stok pengujian akan dibersihkan.');" class="inline">
                            <input type="hidden" name="aksi" value="reset_database_demo">
                            <button type="submit" class="px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer" title="Kembalikan database inventori ke kondisi bersih awal (Baseline)">
                                <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                <span>Reset Data Demo</span>
                            </button>
                        </form>

                        <button onclick="document.getElementById('modal_mulai_sesi').classList.remove('hidden')" class="px-5 py-2.5 rounded-xl bg-zc hover:bg-zcHv text-white text-xs font-bold transition shadow-md shadow-zc/20 flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                            <span>Mulai Sesi Stok Opname Baru (Bekukan Transaksi)</span>
                        </button>
                    <?php else: ?>"""

assert landing_old in content, "landing_old not found!"
content = content.replace(landing_old, landing_new, 1)

# 3. Add link to Dokumentasi Testing in Active Session Header as well
active_btn_old = """                    <?php if ($stats['belum_hitung'] > 0): ?>
                        <!-- Tombol Konfirmasi Simpan Hitungan Sekaligus ke Server (Bisa oleh Kasir, Admin, Superadmin) -->"""

active_btn_new = """                    <!-- Link Cepat Dokumentasi Testing Bab 4 -->
                    <a href="dokumentasi_testing.php" target="_blank" class="px-3 py-2 rounded-xl border border-zc text-zc hover:bg-zc hover:text-white text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <span>Dokumentasi Uji Coba</span>
                    </a>

                    <?php if ($stats['belum_hitung'] > 0): ?>
                        <!-- Tombol Konfirmasi Simpan Hitungan Sekaligus ke Server (Bisa oleh Kasir, Admin, Superadmin) -->"""

assert active_btn_old in content, "active_btn_old not found!"
content = content.replace(active_btn_old, active_btn_new, 1)

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("inventori/stok_opname.php updated with Reset & Dokumentasi buttons!")

