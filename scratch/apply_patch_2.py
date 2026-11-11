with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update Top Action Buttons (lines 1326-1341)
top_btn_old = """                <div class="flex items-center gap-2.5 self-start md:self-auto shrink-0 flex-wrap">
                    <?php if ($isSuperadmin): ?>
                        <?php if ($stats['belum_hitung'] > 0): ?>
                            <!-- Tombol Demo: Hanya mengisi angka input di layar (belum disimpan ke database) -->
                            <button type="button" onclick="isiCepatAngkaDemo()" class="px-3 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Pintasan Demo: Hanya mengisi angka di kotak input pada layar tanpa menyimpan ke server">
                                <svg class="w-3.5 h-3.5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                                <span>Isi Angka Fisik (Demo)</span>
                            </button>

                            <!-- Tombol Konfirmasi Simpan Hitungan Sekaligus ke Server -->
                            <button type="submit" form="form_bulk_simpan" id="btn_bulk_simpan" class="px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer" title="Simpan seluruh hasil hitung fisik yang tertera di tabel sekaligus">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                <span>Simpan Semua Hitungan Fisik</span>
                            </button>
                        <?php endif; ?>

                        <?php 
                        $pendingMutasiCount = $stats['berselisih'] - $stats['sudah_dimutasi'];
                        if ($pendingMutasiCount > 0): 
                        ?>
                            <button type="button" onclick="document.getElementById('modal_rekonsiliasi_masal').classList.remove('hidden')" class="px-3 py-2 rounded-xl border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-800 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Rekonsiliasi sekaligus seluruh <?= $pendingMutasiCount ?> selisih yang terdeteksi">
                                <svg class="w-3.5 h-3.5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="m9 14 2 2 4-4"/></svg>
                                <span>Rekonsiliasi Semua Selisih (<?= $pendingMutasiCount ?>)</span>
                            </button>
                        <?php endif; ?>

                        <button onclick="document.getElementById('modal_batal_sesi').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition cursor-pointer" title="Jalur Darurat jika sesi perlu dibatalkan">
                            Batal Sesi
                        </button>

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
                    <?php endif; ?>
                </div>"""

top_btn_new = """                <div class="flex items-center gap-2.5 self-start md:self-auto shrink-0 flex-wrap">
                    <?php if ($stats['belum_hitung'] > 0): ?>
                        <!-- Tombol Konfirmasi Simpan Hitungan Sekaligus ke Server (Bisa oleh Kasir, Admin, Superadmin) -->
                        <button type="submit" form="form_bulk_simpan" id="btn_bulk_simpan" class="px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer" title="Simpan seluruh hasil hitung fisik yang tertera di tabel sekaligus">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            <span>Simpan Semua Hitungan Fisik</span>
                        </button>
                    <?php endif; ?>

                    <?php if ($canVerify && $stats['belum_hitung'] > 0): ?>
                        <!-- Tombol Demo Khusus Verifikator (Admin/Superadmin): Mengisi angka simulasi di layar -->
                        <button type="button" onclick="isiCepatAngkaDemo()" class="px-3 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Pintasan Demo: Hanya mengisi angka di kotak input pada layar tanpa menyimpan ke server">
                            <svg class="w-3.5 h-3.5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                            <span>Isi Angka Fisik (Demo)</span>
                        </button>
                    <?php endif; ?>

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
                        <button onclick="document.getElementById('modal_batal_sesi').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition cursor-pointer" title="Jalur Darurat jika sesi perlu dibatalkan">
                            Batal Sesi
                        </button>

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
                    <?php endif; ?>
                </div>"""

assert top_btn_old in content, "top_btn_old not found!"
content = content.replace(top_btn_old, top_btn_new, 1)

# 2. Add Kasir Banner and Filter Nav
filter_panel_old = """            <!-- Panel Filter & Navigasi Baris -->
            <div class="bg-white border border-zcBrd rounded-2xl p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                <form method="GET" class="flex flex-wrap items-center gap-2 flex-1">
                    <input type="hidden" name="komoditas" value="<?= htmlspecialchars($filterKom) ?>">
                    <input type="text" name="q" value="<?= htmlspecialchars($searchQ) ?>" placeholder="Cari nama obat, alkes, atau batch..." class="border border-zcBrd rounded-xl px-3 py-1.5 text-xs focus:ring-1 focus:ring-zc flex-1 min-w-[200px]">
                    <select name="status_eval" onchange="this.form.submit()" class="border border-zcBrd rounded-xl px-2.5 py-1.5 text-xs bg-white">
                        <option value="semua" <?= $filterSts === 'semua' ? 'selected' : '' ?>>Semua Status Evaluasi</option>
                        <option value="belum_hitung" <?= $filterSts === 'belum_hitung' ? 'selected' : '' ?>>Belum Dihitung (<?= $stats['belum_hitung'] ?>)</option>
                        <option value="sesuai" <?= $filterSts === 'sesuai' ? 'selected' : '' ?>>Sesuai / 0 Selisih (<?= $stats['sesuai'] ?>)</option>
                        <option value="berselisih_belum_mutasi" <?= $filterSts === 'berselisih_belum_mutasi' ? 'selected' : '' ?>>Selisih Belum Dimutasi (<?= $stats['berselisih'] - $stats['sudah_dimutasi'] ?>)</option>
                        <option value="sudah_dimutasi" <?= $filterSts === 'sudah_dimutasi' ? 'selected' : '' ?>>Sudah Dimutasi (<?= $stats['sudah_dimutasi'] ?>)</option>
                    </select>
                    <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold transition cursor-pointer">Filter</button>
                    <?php if ($searchQ !== '' || $filterKom !== 'semua' || $filterSts !== 'semua'): ?>
                        <a href="stok_opname.php" class="text-rose-600 hover:underline font-semibold ml-1">Reset</a>
                    <?php endif; ?>
                </form>

                <div class="text-[11px] text-zcMut">
                    Menampilkan <strong><?= count($detailItems) ?></strong> dari total <?= $stats['total'] ?> baris opname
                </div>
            </div>"""

filter_panel_new = """            <!-- Banner Identitas & Batasan Hak Akses Kasir (Blind Count Audit Separation) -->
            <?php if ($isKasir): ?>
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs shadow-2xs flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 font-bold">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div class="leading-relaxed">
                        <div class="font-bold text-amber-950 flex items-center gap-2">
                            <span>PERAN STAF KASIR: PENCACAHAN FISIK RAK (BLIND COUNT)</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-200 text-amber-900 font-mono font-bold">Pemisahan Tugas Audit</span>
                        </div>
                        <p class="text-[11px] text-amber-800 mt-0.5">
                            Anda bertugas menghitung dan mencatat kuantitas fisik riil di rak etalase secara independen dalam kondisi baik/layak jual. Sesuai standar audit <em>Blind Count</em>, angka pembukuan sistem dan selisih dirahasiakan dari antarmuka ini. Hasil hitungan Anda akan diverifikasi langsung oleh Admin atau Superadmin.
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Bilah Pemindai Barcode / Fast SKU Locator -->
            <div class="p-3.5 bg-white border border-zcBrd rounded-2xl flex flex-wrap items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5v14M8 5v14M12 5v14M17 5v14M21 5v14"/></svg>
                    </div>
                    <div>
                        <div class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                            <span>Pemindai Barcode / Pencarian Cepat Rak</span>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-mono bg-amber-100 text-amber-800 font-bold border border-amber-200">Auto-Focus</span>
                        </div>
                        <p class="text-[10px] text-slate-500">Scan barcode fisik / ketikkan SKU, Batch, atau SN untuk langsung menyorot dan mengisi kotak hitung fisik.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-1 max-w-md">
                    <input type="text" id="barcode_scan_input" placeholder="Scan Barcode / SKU / No. Batch / SN..." class="w-full border-2 border-amber-300 focus:border-amber-600 rounded-xl px-3 py-1.5 text-xs font-mono font-bold bg-white text-slate-900 shadow-2xs outline-none">
                    <button type="button" onclick="handleBarcodeScan()" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shrink-0 cursor-pointer flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <span>Cari / Sorot</span>
                    </button>
                </div>
            </div>

            <!-- Tab Navigasi Status Evaluasi (Fokus Anomali Selisih Sesuai Arahan Dosen) -->
            <div class="flex items-center gap-2 border-b border-zcBrd pb-2 overflow-x-auto">
                <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=berselisih&q=<?= urlencode($searchQ) ?>" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?= $filterSts === 'berselisih' ? 'bg-rose-600 text-white shadow-2xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' ?>" title="Hanya tampilkan barang yang memiliki selisih fisik (kurang atau lebih)">
                    <span>⚠️ Anomali Selisih (Fokus Verifikasi)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono <?= $filterSts === 'berselisih' ? 'bg-white text-rose-700 font-bold' : 'bg-rose-100 text-rose-800' ?>"><?= $stats['berselisih'] ?></span>
                </a>
                <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=belum_hitung&q=<?= urlencode($searchQ) ?>" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?= $filterSts === 'belum_hitung' ? 'bg-amber-600 text-white shadow-2xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' ?>">
                    <span>⏳ Belum Dihitung</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono <?= $filterSts === 'belum_hitung' ? 'bg-white text-amber-700 font-bold' : 'bg-amber-100 text-amber-800' ?>"><?= $stats['belum_hitung'] ?></span>
                </a>
                <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=sesuai&q=<?= urlencode($searchQ) ?>" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?= $filterSts === 'sesuai' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' ?>" title="Barang yang jumlah fisik dan sistemnya cocok (0 selisih)">
                    <span>✓ Sesuai / Cocok (Disembunyikan)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono <?= $filterSts === 'sesuai' ? 'bg-white text-emerald-700 font-bold' : 'bg-emerald-100 text-emerald-800' ?>"><?= $stats['sesuai'] ?></span>
                </a>
                <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=semua&q=<?= urlencode($searchQ) ?>" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?= $filterSts === 'semua' ? 'bg-slate-800 text-white shadow-2xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' ?>">
                    <span>📋 Semua Data Lengkap</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono <?= $filterSts === 'semua' ? 'bg-white text-slate-900 font-bold' : 'bg-slate-200 text-slate-800' ?>"><?= $stats['total'] ?></span>
                </a>
            </div>

            <!-- Notice Spesifik Saat Mode Fokus Verifikasi Anomali Aktif -->
            <?php if ($filterSts === 'berselisih'): ?>
                <div class="p-3 bg-rose-50/70 border border-rose-200 rounded-xl flex items-center justify-between gap-3 text-xs text-rose-900">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0"></span>
                        <span><strong>Fokus Anomali:</strong> Menampilkan <strong><?= count($detailItems) ?> barang yang memiliki selisih fisik</strong> (Sebanyak <strong><?= $stats['sesuai'] ?> barang yang cocok/sesuai disembunyikan secara otomatis</strong> dari tabel verifikasi agar Anda langsung fokus menyelesaikan penyesuaian selisih).</span>
                    </div>
                    <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=semua&q=<?= urlencode($searchQ) ?>" class="text-[11px] font-bold text-rose-700 hover:underline shrink-0">Tampilkan Semua &rarr;</a>
                </div>
            <?php endif; ?>

            <!-- Panel Filter & Navigasi Baris -->
            <div class="bg-white border border-zcBrd rounded-2xl p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                <form method="GET" class="flex flex-wrap items-center gap-2 flex-1">
                    <input type="hidden" name="komoditas" value="<?= htmlspecialchars($filterKom) ?>">
                    <input type="text" name="q" value="<?= htmlspecialchars($searchQ) ?>" placeholder="Cari nama obat, alkes, barcode, atau batch..." class="border border-zcBrd rounded-xl px-3 py-1.5 text-xs focus:ring-1 focus:ring-zc flex-1 min-w-[200px]">
                    <select name="status_eval" onchange="this.form.submit()" class="border border-zcBrd rounded-xl px-2.5 py-1.5 text-xs bg-white font-medium">
                        <option value="berselisih" <?= $filterSts === 'berselisih' ? 'selected' : '' ?>>⚠️ Selisih Saja (Minus &amp; Plus) (<?= $stats['berselisih'] ?>)</option>
                        <option value="belum_hitung" <?= $filterSts === 'belum_hitung' ? 'selected' : '' ?>>Belum Dihitung (<?= $stats['belum_hitung'] ?>)</option>
                        <option value="sesuai" <?= $filterSts === 'sesuai' ? 'selected' : '' ?>>Sesuai / 0 Selisih (<?= $stats['sesuai'] ?>)</option>
                        <option value="berselisih_belum_mutasi" <?= $filterSts === 'berselisih_belum_mutasi' ? 'selected' : '' ?>>Selisih Belum Dimutasi (<?= $stats['berselisih'] - $stats['sudah_dimutasi'] ?>)</option>
                        <option value="sudah_dimutasi" <?= $filterSts === 'sudah_dimutasi' ? 'selected' : '' ?>>Sudah Dimutasi (<?= $stats['sudah_dimutasi'] ?>)</option>
                        <option value="semua" <?= $filterSts === 'semua' ? 'selected' : '' ?>>Semua Data Lengkap</option>
                    </select>
                    <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold transition cursor-pointer">Filter</button>
                    <?php if ($searchQ !== '' || $filterKom !== 'semua' || $filterSts !== 'berselisih'): ?>
                        <a href="stok_opname.php" class="text-rose-600 hover:underline font-semibold ml-1">Reset</a>
                    <?php endif; ?>
                </form>

                <div class="text-[11px] text-zcMut">
                    Menampilkan <strong><?= count($detailItems) ?></strong> dari total <?= $stats['total'] ?> baris opname
                </div>
            </div>"""

assert filter_panel_old in content, "filter_panel_old not found!"
content = content.replace(filter_panel_old, filter_panel_new, 1)

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Part 2 updated successfully.")

