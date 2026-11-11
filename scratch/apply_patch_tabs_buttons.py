import re

with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update Tahap 2 Filter SQL in lines ~1391-1405
old_filter_sql = """    if ($filterSts === 'berselisih') {
        // HANYA barang yang berselisih (kurang atau lebih), menyembunyikan yang cocok (selisih = 0)
        $where[] = "dob.hitung_fisik IS NOT NULL AND dob.selisih != 0";
    } elseif ($filterSts === 'belum_hitung') {
        $where[] = "dob.hitung_fisik IS NULL";
    } elseif ($filterSts === 'sudah_hitung') {
        $where[] = "dob.hitung_fisik IS NOT NULL";
    } elseif ($filterSts === 'sesuai') {
        $where[] = "dob.hitung_fisik IS NOT NULL AND dob.selisih = 0";
    } elseif ($filterSts === 'berselisih_belum_mutasi') {
        $where[] = "dob.hitung_fisik IS NOT NULL AND dob.selisih != 0 AND dob.status_mutasi = 'Belum Dimutasi'";
    } elseif ($filterSts === 'sudah_dimutasi') {
        $where[] = "dob.status_mutasi = 'Sudah Dimutasi'";
    }"""

new_filter_sql = """    if ($filterSts === 'berselisih' || $filterSts === 'berselisih_belum_mutasi') {
        // HANYA barang yang berselisih dan belum selesai direkonsiliasi
        $where[] = "dob.hitung_fisik IS NOT NULL AND (dob.selisih != 0 OR COALESCE(dob.fisik_rusak, 0) > 0 OR COALESCE(dob.fisik_expired, 0) > 0) AND dob.status_mutasi != 'Sudah Dimutasi'";
    } elseif ($filterSts === 'belum_hitung') {
        $where[] = "dob.hitung_fisik IS NULL";
    } elseif ($filterSts === 'sudah_hitung') {
        $where[] = "dob.hitung_fisik IS NOT NULL";
    } elseif ($filterSts === 'sesuai') {
        $where[] = "dob.hitung_fisik IS NOT NULL AND dob.selisih = 0 AND COALESCE(dob.fisik_rusak, 0) = 0 AND COALESCE(dob.fisik_expired, 0) = 0";
    } elseif ($filterSts === 'sudah_dimutasi') {
        $where[] = "dob.status_mutasi = 'Sudah Dimutasi'";
    }"""

if old_filter_sql in content:
    content = content.replace(old_filter_sql, new_filter_sql)
    print("✓ Filter SQL replaced successfully.")
else:
    print("! Filter SQL pattern not found, checking...")

# 2. Add Tabs for Tahap 2 Verifikasi
old_notice_block = """                <!-- Notice Otomatisasi Filter Anomali Selisih -->
                <?php if ($filterSts === 'berselisih'): ?>
                    <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-600 shrink-0"></span>
                            <span><strong>Fokus Anomali Terfilter Otomatis:</strong> Menampilkan <strong><?= count($detailItems) ?> barang dengan selisih/anomali fisik</strong>. Sebanyak <strong><?= $stats['sesuai'] ?> barang yang cocok (0 selisih) otomatis disembunyikan</strong> agar Anda langsung fokus menyelesaikan rekonsiliasi.</span>
                        </div>
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=semua&q=<?= urlencode($searchQ) ?>" class="text-[11px] font-bold text-slate-900 underline hover:text-black shrink-0">Tampilkan Semua Data Termasuk yang Cocok &rarr;</a>
                    </div>
                <?php else: ?>
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-3 text-xs text-slate-700">
                        <span>Menampilkan seluruh <?= count($detailItems) ?> data persediaan.</span>
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=berselisih&q=<?= urlencode($searchQ) ?>" class="text-[11px] font-bold text-rose-700 underline shrink-0">&larr; Kembali ke Fokus Anomali Saja</a>
                    </div>
                <?php endif; ?>"""

new_tabs_block = """                <!-- TAB NAVIGASI TAHAP 2 VERIFIKASI (SUPERADMIN & ADMIN) -->
                <div class="flex items-center gap-2 border-b border-slate-200 pb-2 mb-3 overflow-x-auto text-xs">
                    <!-- Tab 1: Perlu Mutasi (Berselisih) -->
                    <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=berselisih&q=<?= urlencode($searchQ) ?>" 
                       class="px-3.5 py-1.5 rounded-xl font-bold flex items-center gap-1.5 transition <?= ($filterSts === 'berselisih' || $filterSts === 'berselisih_belum_mutasi') ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>" title="Fokus verifikasi pada barang yang berselisih">
                        <span>Perlu Mutasi (Berselisih)</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono <?= ($filterSts === 'berselisih' || $filterSts === 'berselisih_belum_mutasi') ? 'bg-rose-500 text-white' : 'bg-slate-200 text-slate-700' ?>">
                            <?= max(0, $stats['berselisih'] - $stats['sudah_dimutasi']) ?>
                        </span>
                    </a>

                    <!-- Tab 2: Sesuai / Pas (Klop) -->
                    <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=sesuai&q=<?= urlencode($searchQ) ?>" 
                       class="px-3.5 py-1.5 rounded-xl font-bold flex items-center gap-1.5 transition <?= $filterSts === 'sesuai' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>" title="Daftar barang yang jumlah fisiknya cocok dengan sistem">
                        <span>Sesuai (Klop)</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono <?= $filterSts === 'sesuai' ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-700' ?>">
                            <?= $stats['sesuai'] ?>
                        </span>
                    </a>

                    <!-- Tab 3: Sudah Dimutasi -->
                    <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=sudah_dimutasi&q=<?= urlencode($searchQ) ?>" 
                       class="px-3.5 py-1.5 rounded-xl font-bold flex items-center gap-1.5 transition <?= $filterSts === 'sudah_dimutasi' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>" title="Barang yang sudah selesai direkonsiliasi dan dicatat ke kartu stok">
                        <span>Sudah Dimutasi</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono <?= $filterSts === 'sudah_dimutasi' ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-700' ?>">
                            <?= $stats['sudah_dimutasi'] ?>
                        </span>
                    </a>

                    <!-- Tab 4: Semua Item (Hanya untuk Superadmin & Admin untuk audit lengkap) -->
                    <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=semua&q=<?= urlencode($searchQ) ?>" 
                       class="px-3.5 py-1.5 rounded-xl font-bold flex items-center gap-1.5 transition <?= $filterSts === 'semua' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>" title="Tampilkan seluruh entitas persediaan">
                        <span>Semua Item</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono <?= $filterSts === 'semua' ? 'bg-slate-700 text-white' : 'bg-slate-200 text-slate-700' ?>">
                            <?= $stats['total'] ?>
                        </span>
                    </a>
                </div>

                <!-- Info Keterangan Tab Aktif -->
                <?php if ($filterSts === 'berselisih' || $filterSts === 'berselisih_belum_mutasi'): ?>
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-2 text-xs text-slate-800 mb-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-600 shrink-0"></span>
                        <span><strong>Fokus Anomali:</strong> Menampilkan <strong><?= count($detailItems) ?> barang dengan selisih fisik</strong>. Sebanyak <strong><?= $stats['sesuai'] ?> barang yang cocok disembunyikan</strong> agar Anda langsung fokus menyelesaikan rekonsiliasi.</span>
                    </div>
                <?php elseif ($filterSts === 'sesuai'): ?>
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-2 text-xs text-emerald-800 mb-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 shrink-0"></span>
                        <span><strong>Daftar Barang Sesuai:</strong> Menampilkan <strong><?= count($detailItems) ?> barang yang 100% klop</strong> (stok fisik di rak sama persis dengan stok pembukuan sistem). Tidak memerlukan mutasi.</span>
                    </div>
                <?php elseif ($filterSts === 'sudah_dimutasi'): ?>
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-2 text-xs text-blue-800 mb-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600 shrink-0"></span>
                        <span><strong>Riwayat Selesai Rekonsiliasi:</strong> Menampilkan <strong><?= count($detailItems) ?> barang yang telah selesai dimutasi</strong> dan tersinkronisasi ke kartu stok.</span>
                    </div>
                <?php else: ?>
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-2 text-xs text-slate-700 mb-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-500 shrink-0"></span>
                        <span><strong>Semua Item Persediaan:</strong> Menampilkan seluruh <strong><?= count($detailItems) ?> entitas fisik</strong> sesi opname.</span>
                    </div>
                <?php endif; ?>"""

if old_notice_block in content:
    content = content.replace(old_notice_block, new_tabs_block)
    print("✓ Tahap 2 Tabs added successfully.")
else:
    print("! Tahap 2 Tabs pattern not found, checking...")

# 3. Replace Dangerous 1-Click Buttons in Table Rows with Unified "Verifikasi & Mutasi" Modal Button
old_action_buttons = """                                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                                    <?php if ($fRusak > 0): ?>
                                                        <!-- Tombol 1-Klik Mutasi Rusak -->
                                                        <form method="POST" class="inline" onsubmit="return confirm('Eksekusi Mutasi Barang Rusak sebanyak <?= $fRusak ?> <?= $it['nama_satuan'] ?> untuk <?= addslashes($it['nama_produk']) ?>? Stok akan dipotong dan dicatat di Kartu Stok.');">
                                                            <input type="hidden" name="aksi" value="mutasi_1klik_rusak">
                                                            <input type="hidden" name="id_detail" value="<?= $it['id'] ?>">
                                                            <button type="submit" class="px-2 py-1 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-[10px] font-bold transition shadow-2xs cursor-pointer" title="Eksekusi 1-Klik Mutasi Rusak">
                                                                Mutasi Rusak (<?= $fRusak ?>)
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($fExp > 0): ?>
                                                        <!-- Tombol 1-Klik Mutasi Expired -->
                                                        <form method="POST" class="inline" onsubmit="return confirm('Eksekusi Mutasi Barang Kedaluwarsa sebanyak <?= $fExp ?> <?= $it['nama_satuan'] ?> untuk <?= addslashes($it['nama_produk']) ?>? Stok akan dipotong dan dicatat di Kartu Stok.');">
                                                            <input type="hidden" name="aksi" value="mutasi_1klik_expired">
                                                            <input type="hidden" name="id_detail" value="<?= $it['id'] ?>">
                                                            <button type="submit" class="px-2 py-1 rounded-lg bg-rose-700 hover:bg-rose-800 text-white text-[10px] font-bold transition shadow-2xs cursor-pointer" title="Eksekusi 1-Klik Mutasi Expired">
                                                                Mutasi Expired (<?= $fExp ?>)
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($selisih < 0): ?>
                                                        <!-- Buka Modal Mutasi Defisit / Hilang -->
                                                        <button type="button" onclick="openModalMutasi(<?= htmlspecialchars(json_encode($it)) ?>)" class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[10px] font-bold transition shadow-2xs cursor-pointer">
                                                            Mutasi Hilang
                                                        </button>
                                                    <?php elseif ($selisih > 0): ?>
                                                        <!-- Buka Modal Mutasi Koreksi Lebih -->
                                                        <button type="button" onclick="openModalMutasi(<?= htmlspecialchars(json_encode($it)) ?>)" class="px-2 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold transition shadow-2xs cursor-pointer">
                                                            Koreksi Lebih
                                                        </button>
                                                    <?php endif; ?>
                                                </div>"""

new_action_buttons = """                                                <div class="flex items-center justify-center gap-1.5">
                                                    <button type="button" 
                                                            onclick="openModalMutasi(<?= htmlspecialchars(json_encode($it)) ?>)" 
                                                            class="h-7 px-3 rounded-lg <?= $selisih > 0 ? 'bg-blue-600 hover:bg-blue-700' : ($fRusak > 0 ? 'bg-amber-600 hover:bg-amber-700' : ($fExp > 0 ? 'bg-rose-600 hover:bg-rose-700' : 'bg-slate-900 hover:bg-slate-800')) ?> text-white text-[11px] font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Buka dialog konfirmasi untuk meninjau dan mengeksekusi mutasi">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                                        <span>
                                                            <?php if ($selisih > 0): ?>
                                                                Koreksi Surplus (+<?= $selisih ?>)
                                                            <?php elseif ($fRusak > 0): ?>
                                                                Verifikasi Rusak (-<?= $fRusak ?>)
                                                            <?php elseif ($fExp > 0): ?>
                                                                Verifikasi Expired (-<?= $fExp ?>)
                                                            <?php else: ?>
                                                                Verifikasi Hilang (<?= $selisih ?>)
                                                            <?php endif; ?>
                                                        </span>
                                                    </button>
                                                </div>"""

if old_action_buttons in content:
    content = content.replace(old_action_buttons, new_action_buttons)
    print("✓ Unified action buttons replaced successfully.")
else:
    print("! Unified action buttons pattern not found, checking...")

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Applied patch part 1.")

