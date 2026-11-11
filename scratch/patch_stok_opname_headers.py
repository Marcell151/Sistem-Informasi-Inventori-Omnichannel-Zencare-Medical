# Full patch for inventori/stok_opname.php
with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update Landing Header (around line 1500)
old_landing_header = """            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-zcBrd pb-5 mb-5">
                <div>
                    <h2 class="text-base font-bold text-zcTxt">Status Sistem: Normal (Semua Transaksi Aktif)</h2>
                    <p class="text-xs text-zcMut mt-1 leading-relaxed">
                        Tidak ada sesi opname yang sedang berjalan. Kasir POS offline, toko online e-commerce, dan modul logistik beroperasi normal.
                    </p>
                </div>

                <div class="flex items-center gap-2.5 flex-wrap">
                    <!-- Tombol Panduan & Dokumentasi Pengujian Black-Box -->
                    <a href="dokumentasi_testing.php" target="_blank" class="px-4 py-2.5 rounded-xl border border-zc text-zc hover:bg-zc hover:text-white text-xs font-bold transition flex items-center justify-center gap-2 shadow-2xs" title="Buka dokumentasi rekaman pengujian black-box modul stok opname">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                        <span>Dokumentasi Pengujian Black-Box</span>
                    </a>

                    <?php if ($canVerify): ?>
                        <!-- Tombol Reset Database Demo (Utilitas Khusus Penguji / Demo Sidang) -->
                        <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memulihkan database inventori ke kondisi bersih awal (Baseline)? Seluruh sesi uji coba dan mutasi sementara akan dibersihkan.');" class="inline">
                            <input type="hidden" name="aksi" value="reset_database_demo">
                            <button type="submit" class="px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer" title="Memulihkan database inventori ke kondisi awal bersih sebelum atau sesudah demonstrasi pengujian.">
                                <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                <span>Reset Data Demo</span>
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($isSuperadmin): ?>
                        <button onclick="document.getElementById('modal_mulai_sesi').classList.remove('hidden')" class="px-5 py-2.5 rounded-xl bg-zc hover:bg-zcHv text-white text-xs font-bold transition shadow-md shadow-zc/20 flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                            <span>Mulai Sesi Stok Opname Baru (Bekukan Transaksi)</span>
                        </button>
                    <?php else: ?>
                        <div class="text-[11px] bg-slate-50 border border-slate-200 px-3 py-2 rounded-xl text-slate-500 font-medium">
                            Hanya Superadmin yang berwenang memulai Sesi Stok Opname.
                        </div>
                    <?php endif; ?>
                </div>
            </div>"""

new_landing_header = """            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-zcBrd pb-5 mb-5">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                        <h2 class="text-base font-bold text-zcTxt">Status Sistem: Normal (Semua Transaksi Aktif)</h2>
                    </div>
                    <p class="text-xs text-zcMut mt-1 leading-relaxed">
                        Tidak ada sesi opname yang sedang berjalan. Kasir POS offline, toko online e-commerce, dan modul logistik beroperasi normal.
                    </p>
                </div>

                <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                    <!-- Tombol Panduan & Dokumentasi Pengujian Black-Box -->
                    <a href="dokumentasi_testing.php" target="_blank" class="h-9 px-3.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center justify-center gap-2 shadow-2xs" title="Buka dokumentasi rekaman pengujian black-box modul stok opname">
                        <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                        <span>Dokumentasi Pengujian Black-Box</span>
                    </a>

                    <?php if ($canVerify): ?>
                        <!-- Tombol Reset Database Demo -->
                        <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memulihkan database inventori ke kondisi bersih awal (Baseline)? Seluruh sesi uji coba dan mutasi sementara akan dibersihkan.');" class="inline">
                            <input type="hidden" name="aksi" value="reset_database_demo">
                            <button type="submit" class="h-9 px-3.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer" title="Memulihkan database inventori ke kondisi awal bersih sebelum atau sesudah demonstrasi pengujian.">
                                <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                <span>Reset Data Demo</span>
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($isSuperadmin): ?>
                        <button onclick="document.getElementById('modal_mulai_sesi').classList.remove('hidden')" class="h-9 px-4 rounded-xl bg-zc hover:bg-zcHv text-white text-xs font-bold transition shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                            <span>Mulai Sesi Stok Opname Baru (Bekukan Transaksi)</span>
                        </button>
                    <?php else: ?>
                        <div class="h-9 px-3 rounded-xl bg-slate-100 border border-slate-200 text-slate-600 text-xs font-medium flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span>Akses Mulai Sesi: Khusus Superadmin</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>"""

if old_landing_header in content:
    content = content.replace(old_landing_header, new_landing_header)
    print("Landing header updated successfully!")
else:
    print("Warning: old_landing_header not found verbatim")

# 2. Update Active Session Top Header (around line 1696)
old_active_banner = """            <!-- Banner Utama Sesi Aktif (Clean White ZenCare Design) -->
            <div class="p-5 rounded-2xl bg-white border border-zcBrd shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs bg-slate-100 text-slate-800 border border-slate-300 font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider font-mono">
                                Sesi: <?= htmlspecialchars($activeSession['kode_sesi']) ?>
                            </span>
                            <?php if ($currentTahap === 'Penghitungan'): ?>
                                <span class="text-xs bg-amber-50 text-amber-800 border border-amber-200 font-bold px-2.5 py-0.5 rounded-full">
                                    Tahap 1: Penghitungan Fisik (Blind Count)
                                </span>
                            <?php else: ?>
                                <span class="text-xs bg-blue-50 text-blue-800 border border-blue-200 font-bold px-2.5 py-0.5 rounded-full">
                                    Tahap 2: Verifikasi &amp; Rekonsiliasi Selisih
                                </span>
                            <?php endif; ?>
                            <span class="text-[11px] text-zcMut">Dimulai: <?= date('d/m/Y H:i', strtotime($activeSession['dibekukan_pada'])) ?> WIB</span>
                        </div>
                        <p class="text-xs text-zcMut mt-1 leading-relaxed">
                            Status Server: Transaksi kasir POS, checkout toko daring, konversi UOM, dan penerimaan logistik sedang <strong class="text-zcTxt font-bold">DIBEKUKAN</strong>.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 self-start md:self-auto shrink-0 flex-wrap">
                    <!-- Link Cepat Dokumentasi Pengujian Black-Box -->
                    <a href="dokumentasi_testing.php" target="_blank" class="px-3 py-2 rounded-xl border border-zc text-zc hover:bg-zc hover:text-white text-xs font-bold transition flex items-center gap-1.5 shadow-2xs" title="Buka rekaman dokumentasi pengujian black-box">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <span>Dokumentasi Pengujian</span>
                    </a>

                    <?php if ($currentTahap === 'Penghitungan'): ?>
                        <?php if ($canCountPhysical && $stats['belum_hitung'] > 0): ?>
                            <!-- Tombol Konfirmasi Simpan Hitungan Sekaligus ke Server (Kasir & Superadmin) -->
                            <button type="submit" form="form_bulk_simpan" id="btn_bulk_simpan" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer" title="Simpan seluruh hasil hitung fisik yang tertera di tabel sekaligus">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                <span>Simpan Semua Hitungan Fisik</span>
                            </button>
                        <?php endif; ?>

                        <?php if ($canVerify && $stats['belum_hitung'] > 0): ?>
                            <!-- Tombol Auto-Generate Skenario Pengujian (Khusus Superadmin) -->
                            <form method="POST" class="inline" onsubmit="return confirm('Jalankan Pengisian Fisik Otomatis untuk Simulasi? Sistem akan mengisi 121 baris fisik dengan 5 anomali terencana (Cefadroxil rusak, Sanmol expired, Amoxicillin hilang, Omron hilang, Betadine surplus) dan 116 item cocok.');">
                                <input type="hidden" name="aksi" value="isi_cepat_demo">
                                <button type="submit" class="px-3 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Pintasan Simulasi: 121 hasil fisik dengan 5 anomali terencana">
                                    <svg class="w-3.5 h-3.5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                                    <span>Simulasi Input Fisik Lengkap (121 Item)</span>
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if ($canVerify): ?>
                            <!-- Tombol Tutup Penghitungan & Buka Verifikasi -->
                            <form method="POST" class="inline" onsubmit="return confirm('Tutup tahap penghitungan fisik? Seluruh formulir pencacahan akan dikunci dan antarmuka beralih ke Verifikasi Selisih.');">
                                <input type="hidden" name="aksi" value="tutup_penghitungan">
                                <button type="submit" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                    <span>Tutup Penghitungan &amp; Buka Verifikasi</span>
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- TAHAP VERIFIKASI -->
                        <?php 
                        $pendingMutasiCount = $stats['berselisih'] - $stats['sudah_dimutasi'];
                        if ($canVerify && $pendingMutasiCount > 0): 
                        ?>
                            <button type="button" onclick="document.getElementById('modal_rekonsiliasi_masal').classList.remove('hidden')" class="px-3 py-2 rounded-xl border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-800 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Rekonsiliasi sekaligus seluruh <?= $pendingMutasiCount ?> selisih yang terdeteksi">
                                <svg class="w-3.5 h-3.5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="m9 14 2 2 4-4"/></svg>
                                <span>Rekonsiliasi Semua Selisih (<?= $pendingMutasiCount ?>)</span>
                            </button>
                        <?php endif; ?>

                        <?php if ($isSuperadmin): ?>
                            <?php 
                            $bisaSelesai = ($stats['belum_hitung'] === 0 && ($stats['berselisih'] - $stats['sudah_dimutasi']) === 0);
                            ?>
                            <form method="POST" onsubmit="return confirm('Apakah Anda yakin seluruh hasil fisik sudah diverifikasi dan ingin menutup sesi opname ini? Pembekuan sistem akan dicabut.');">
                                <input type="hidden" name="aksi" value="selesaikan_sesi">
                                <button type="submit" <?= !$bisaSelesai ? 'disabled' : '' ?> class="px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5 <?= $bisaSelesai ? 'bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer' : 'bg-slate-100 border border-slate-200 text-slate-400 cursor-not-allowed' ?>" title="<?= !$bisaSelesai ? 'Sesi hanya bisa diselesaikan jika semua item sudah dihitung dan semua selisih sudah dimutasi!' : 'Buka kembali sistem normal' ?>">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                    <span>Selesaikan Sesi (Buka Kunci)</span>
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-500 text-[11px] font-medium">
                                Menunggu Superadmin membuka kunci
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($isSuperadmin): ?>
                        <button onclick="document.getElementById('modal_batal_sesi').classList.remove('hidden')" class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition cursor-pointer" title="Jalur Darurat jika sesi perlu dibatalkan">
                            Batal Sesi
                        </button>
                    <?php endif; ?>
                </div>
            </div>"""

new_active_banner = """            <!-- Banner Utama Sesi Aktif (2-Row Clean Stacked Layout - No Narrow Squishing) -->
            <div class="bg-white border border-zcBrd rounded-2xl p-5 shadow-sm space-y-4">
                <!-- Baris 1: Informasi Status Sesi & Kunci Server (Full Width) -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 border-b border-slate-100 pb-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs bg-slate-900 text-white font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider font-mono">
                                    Sesi: <?= htmlspecialchars($activeSession['kode_sesi']) ?>
                                </span>
                                <?php if ($currentTahap === 'Penghitungan'): ?>
                                    <span class="text-xs bg-amber-100 text-amber-900 border border-amber-300 font-bold px-2.5 py-0.5 rounded-full">
                                        Tahap 1: Penghitungan Fisik (Blind Count)
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs bg-blue-100 text-blue-900 border border-blue-300 font-bold px-2.5 py-0.5 rounded-full">
                                        Tahap 2: Verifikasi &amp; Rekonsiliasi Selisih
                                    </span>
                                <?php endif; ?>
                                <span class="text-xs text-slate-500 font-medium">
                                    Dimulai: <strong><?= date('d/m/Y H:i', strtotime($activeSession['dibekukan_pada'])) ?> WIB</strong>
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 mt-1">
                                Status Server: Transaksi kasir POS, checkout toko daring, konversi UOM, dan penerimaan logistik sedang <strong>DIBEKUKAN</strong>.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span>POS &amp; Toko Daring Terkunci (423)</span>
                        </span>
                        <?php if ($isSuperadmin): ?>
                            <button type="button" onclick="document.getElementById('modal_batal_sesi').classList.remove('hidden')" class="text-[11px] text-slate-400 hover:text-rose-600 font-semibold px-2 py-1 rounded transition cursor-pointer" title="Jalur Darurat jika sesi perlu dibatalkan">
                                Batalkan Sesi
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Baris 2: Toolbar Tindakan (Uniform Height h-9, Rapi, Lengkap) -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-0.5">
                    <div class="text-xs text-slate-600">
                        <?php if ($currentTahap === 'Penghitungan'): ?>
                            <span>Progres Hitung: <strong class="text-slate-900 font-mono"><?= $stats['sudah_hitung'] ?></strong> / <?= $stats['total'] ?> item (<?= $stats['total'] > 0 ? round(($stats['sudah_hitung'] / $stats['total']) * 100) : 0 ?>%)</span>
                        <?php else: ?>
                            <span>Rekonsiliasi: <strong class="text-slate-900 font-mono"><?= $stats['sudah_dimutasi'] ?></strong> / <?= $stats['berselisih'] ?> anomali selesai dimutasi</span>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <!-- 1. Dokumentasi Pengujian -->
                        <a href="dokumentasi_testing.php" target="_blank" class="h-9 px-3.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs" title="Buka rekaman dokumentasi pengujian black-box">
                            <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            <span>Dokumentasi Pengujian</span>
                        </a>

                        <?php if ($currentTahap === 'Penghitungan'): ?>
                            <?php if ($canCountPhysical && $stats['belum_hitung'] > 0): ?>
                                <!-- 2. Tombol Cepat Isi Angka Demo (Client-Side JS) -->
                                <button type="button" onclick="isiOtomatisAngkaDemo()" class="h-9 px-3.5 rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-900 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Pintasan Uji Coba: Isi otomatis angka fisik di tabel sesuai skenario demo pengujian">
                                    <svg class="w-3.5 h-3.5 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                    <span>Isi Cepat Angka Uji Coba</span>
                                </button>

                                <!-- 3. Tombol Simpan Semua Hitungan Fisik -->
                                <button type="button" onclick="submitBulkCountingForm()" id="btn_bulk_simpan" class="h-9 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer" title="Simpan seluruh hasil hitung fisik yang tertera di tabel sekaligus">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                    <span>Simpan Semua Hitungan Fisik</span>
                                </button>
                            <?php endif; ?>

                            <?php if ($canVerify): ?>
                                <!-- 4. Tombol Tutup Penghitungan & Buka Verifikasi -->
                                <form method="POST" class="inline" onsubmit="return confirm('Tutup tahap penghitungan fisik? Seluruh formulir pencacahan akan dikunci dan antarmuka beralih ke Verifikasi Selisih.');">
                                    <input type="hidden" name="aksi" value="tutup_penghitungan">
                                    <button type="submit" class="h-9 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                        <span>Tutup Penghitungan &amp; Buka Verifikasi</span>
                                    </button>
                                </form>
                            <?php endif; ?>

                        <?php else: ?>
                            <!-- TAHAP 2: VERIFIKASI -->
                            <?php 
                            $pendingMutasiCount = $stats['berselisih'] - $stats['sudah_dimutasi'];
                            if ($canVerify && $pendingMutasiCount > 0): 
                            ?>
                                <button type="button" onclick="document.getElementById('modal_rekonsiliasi_masal').classList.remove('hidden')" class="h-9 px-3.5 rounded-xl border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-800 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Rekonsiliasi sekaligus seluruh <?= $pendingMutasiCount ?> selisih yang terdeteksi">
                                    <svg class="w-3.5 h-3.5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="m9 14 2 2 4-4"/></svg>
                                    <span>Rekonsiliasi Semua Selisih (<?= $pendingMutasiCount ?>)</span>
                                </button>
                            <?php endif; ?>

                            <?php if ($isSuperadmin): ?>
                                <?php $bisaSelesai = ($stats['belum_hitung'] === 0 && ($stats['berselisih'] - $stats['sudah_dimutasi']) === 0); ?>
                                <form method="POST" onsubmit="return confirm('Apakah Anda yakin seluruh hasil fisik sudah diverifikasi dan ingin menutup sesi opname ini? Pembekuan sistem akan dicabut.');">
                                    <input type="hidden" name="aksi" value="selesaikan_sesi">
                                    <button type="submit" <?= !$bisaSelesai ? 'disabled' : '' ?> class="h-9 px-4 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5 <?= $bisaSelesai ? 'bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer' : 'bg-slate-100 border border-slate-200 text-slate-400 cursor-not-allowed' ?>">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        <span>Selesaikan Sesi (Buka Kunci Sistem)</span>
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="h-9 px-3 rounded-xl bg-slate-100 border border-slate-200 text-slate-500 text-xs font-medium flex items-center gap-1.5">
                                    Menunggu Superadmin membuka kunci
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>"""

if old_active_banner in content:
    content = content.replace(old_active_banner, new_active_banner)
    print("Active session banner updated successfully!")
else:
    print("Warning: old_active_banner not found verbatim")

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Landing and Active Banner patches complete.")

