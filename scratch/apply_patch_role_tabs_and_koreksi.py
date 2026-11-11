import re

with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update filter status logic in lines ~1360-1375 so Admin Toko is strictly locked to 'berselisih'
old_filter_logic = """    if ($filterSts === '') {
        if ($isKasir) {
            $filterSts = ($stats['belum_hitung'] > 0) ? 'belum_hitung' : 'semua';
        } else {
            if ($stats['sudah_hitung'] > 0 && $stats['berselisih'] > 0) {
                $filterSts = 'berselisih';
            } elseif ($stats['belum_hitung'] > 0) {
                $filterSts = 'semua';
            } else {
                $filterSts = 'semua';
            }
        }
    }"""

new_filter_logic = """    // Penegakan Pemisahan Hak Akses Audit (Internal Control & Segregation of Duties)
    // Admin Toko hanya berwenang menyelesaikan anomali selisih nyata yang dilaporkan dari lapangan.
    // Admin Toko terkunci secara mutlak pada daftar selisih ('berselisih') dan tidak dapat melihat/mengubah data yang sudah cocok (klop).
    if ($isAdmin && !$isSuperadmin) {
        $filterSts = 'berselisih';
    } else {
        if ($filterSts === '') {
            if ($isKasir) {
                $filterSts = ($stats['belum_hitung'] > 0) ? 'belum_hitung' : 'semua';
            } else {
                $filterSts = ($activeSession['tahap'] === 'Verifikasi') ? 'berselisih' : 'semua';
            }
        }
    }"""

if old_filter_logic in content:
    content = content.replace(old_filter_logic, new_filter_logic)
    print("✓ Filter logic updated successfully.")
else:
    print("! Filter logic pattern not found!")

# 2. Add PHP action 'koreksi_hitung_superadmin' before 'eksekusi_mutasi_baris'
old_action_target = "    // --- 3. EKSEKUSI MUTASI SELISIH BARIS (Admin & Superadmin) ---"
new_action_code = """    // --- 2.5 KOREKSI HITUNGAN FISIK KHUSUS SUPERADMIN (Jalur Audit & Kesalahan Lapangan) ---
    elseif ($aksi === 'koreksi_hitung_superadmin') {
        $idDetail = intval($_POST['id_detail'] ?? 0);
        $alasanKoreksi = trim($_POST['alasan_koreksi'] ?? '');

        if (!$activeSession) {
            $msg = "Tidak ada sesi opname yang sedang aktif.";
            $msgType = 'error';
        } elseif (!$isSuperadmin) {
            $msg = "Akses ditolak! Koreksi data opname yang sudah sesuai hanya berhak dilakukan oleh Superadmin (Pemilik Toko).";
            $msgType = 'error';
        } elseif (!$idDetail) {
            $msg = "Data baris tidak valid.";
            $msgType = 'error';
        } else {
            try {
                $fBaik = max(0, intval($_POST['koreksi_fisik_baik'] ?? 0));
                $fRusak = max(0, intval($_POST['koreksi_fisik_rusak'] ?? 0));
                $fExp = max(0, intval($_POST['koreksi_fisik_expired'] ?? 0));
                $totalFisik = $fBaik + $fRusak + $fExp;

                // Ambil stok sistem fisik
                $stmtChk = $pdo->prepare("SELECT stok_sistem_fisik FROM detail_opname_batch WHERE id = ? AND id_sesi = ?");
                $stmtChk->execute([$idDetail, $activeSession['id']]);
                $rowChk = $stmtChk->fetch();

                if (!$rowChk) throw new Exception("Data baris opname tidak ditemukan.");

                $stokSistem = intval($rowChk['stok_sistem_fisik']);
                $selisihBaru = $totalFisik - $stokSistem;
                $statusMutasi = ($selisihBaru === 0 && $fRusak === 0 && $fExp === 0) ? 'Sesuai' : 'Belum Dimutasi';

                $stmtUpd = $pdo->prepare("
                    UPDATE detail_opname_batch 
                    SET hitung_fisik = ?,
                        fisik_baik = ?,
                        fisik_rusak = ?,
                        fisik_expired = ?,
                        selisih = ?,
                        status_mutasi = ?,
                        catatan_kondisi = CONCAT(COALESCE(catatan_kondisi, ''), ' [Koreksi Superadmin: ', ?, ']')
                    WHERE id = ? AND id_sesi = ?
                ");
                $stmtUpd->execute([
                    $totalFisik,
                    $fBaik,
                    $fRusak,
                    $fExp,
                    $selisihBaru,
                    $statusMutasi,
                    $alasanKoreksi ?: 'Koreksi kesalahan hitung lapangan',
                    $idDetail,
                    $activeSession['id']
                ]);

                $msg = "Koreksi hitungan fisik berhasil disimpan oleh Superadmin! Data persediaan telah disesuaikan.";
                $msgType = 'success';
            } catch (Exception $e) {
                $msg = "Gagal menyimpan koreksi: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }

    // --- 3. EKSEKUSI MUTASI SELISIH BARIS (Admin & Superadmin) ---"""

if old_action_target in content:
    content = content.replace(old_action_target, new_action_code)
    print("✓ Superadmin action 'koreksi_hitung_superadmin' added successfully.")
else:
    print("! Action target pattern not found!")

# 3. Update Tahap 2 Tabs rendering: only show tabs for Superadmin; Admin Toko gets dedicated notice banner
old_tabs_render = """                <!-- TAB NAVIGASI TAHAP 2 VERIFIKASI (SUPERADMIN & ADMIN) -->
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
                </div>"""

new_tabs_render = """                <!-- NAVIGASI TAHAP 2 VERIFIKASI -->
                <?php if ($isSuperadmin): ?>
                    <!-- TAB NAVIGASI AUDIT KHUSUS SUPERADMIN (PEMILIK TOKO) -->
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

                        <!-- Tab 4: Semua Item (Audit Menyeluruh) -->
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=semua&q=<?= urlencode($searchQ) ?>" 
                           class="px-3.5 py-1.5 rounded-xl font-bold flex items-center gap-1.5 transition <?= $filterSts === 'semua' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>" title="Tampilkan seluruh entitas persediaan">
                            <span>Semua Item</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono <?= $filterSts === 'semua' ? 'bg-slate-700 text-white' : 'bg-slate-200 text-slate-700' ?>">
                                <?= $stats['total'] ?>
                            </span>
                        </a>
                    </div>
                <?php else: ?>
                    <!-- ADMIN TOKO: FOKUS ANOMALI (TANPA TAB) -->
                    <div class="p-3 bg-slate-900 text-white rounded-xl flex items-center justify-between text-xs mb-3 shadow-xs">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            <div>
                                <span class="font-bold">Daftar Rekonsiliasi Selisih Fisik (Admin Toko)</span>
                                <p class="text-[11px] text-slate-300">Menampilkan <?= count($detailItems) ?> barang yang memiliki selisih fisik di rak. Selesaikan verifikasi mutasi agar sesi opname dapat ditutup.</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg bg-rose-500 text-white font-mono font-bold text-xs"><?= count($detailItems) ?> Item Berselisih</span>
                    </div>
                <?php endif; ?>"""

if old_tabs_render in content:
    content = content.replace(old_tabs_render, new_tabs_render)
    print("✓ Tahap 2 Tabs role-gating updated successfully.")
else:
    print("! Tahap 2 Tabs pattern not found!")

# 4. Fix table action column for Sesuai/Klop items so they don't show 'Verifikasi Hilang (0)'
old_table_action = """                                        <td class="py-3 px-3 text-center">
                                            <?php if ($isMutated): ?>
                                                <span class="text-[11px] text-emerald-700 font-semibold">Tuntas Direkonsiliasi</span>
                                            <?php else: ?>
                                                <div class="flex items-center justify-center gap-1.5">
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
                                                </div>
                                            <?php endif; ?>
                                        </td>"""

new_table_action = """                                        <td class="py-3 px-3 text-center">
                                            <?php if ($isMutated): ?>
                                                <span class="text-[11px] text-emerald-700 font-semibold flex items-center justify-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                                    Tuntas Direkonsiliasi
                                                </span>
                                            <?php elseif ($selisih === 0 && $fRusak === 0 && $fExp === 0): ?>
                                                <!-- BARANG SESUAI / KLOP (TIDAK ADA SELISIH) -->
                                                <div class="flex items-center justify-center gap-2">
                                                    <span class="text-[11px] text-emerald-700 font-semibold flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                                        Klop (Sesuai)
                                                    </span>
                                                    <?php if ($isSuperadmin): ?>
                                                        <!-- Khusus Superadmin: Jika ada salah hitung kasir/admin, Superadmin berwenang mengoreksi ulang -->
                                                        <button type="button" 
                                                                onclick="openModalKoreksiSuperadmin(<?= htmlspecialchars(json_encode($it)) ?>)" 
                                                                class="h-6 px-2 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-bold transition flex items-center gap-1 border border-slate-300 cursor-pointer" 
                                                                title="Khusus Pemilik/Superadmin: Koreksi jika terjadi kesalahan input kasir saat menghitung">
                                                            <svg class="w-3 h-3 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                                            <span>Koreksi</span>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <!-- BARANG ANOMALI (PERLU MUTASI) -->
                                                <div class="flex items-center justify-center gap-1.5">
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
                                                </div>
                                            <?php endif; ?>
                                        </td>"""

if old_table_action in content:
    content = content.replace(old_table_action, new_table_action)
    print("✓ Table action column for Sesuai items updated successfully.")
else:
    print("! Table action pattern not found!")

# 5. Add HTML for #modal_koreksi_superadmin before #modal_mutasi
modal_target = '<!-- Modal Eksekusi Mutasi Selisih (Obat vs Alkes & Multi-Alasan Defisit) -->'
modal_koreksi_html = """<!-- Modal Koreksi Ulang Hitungan Fisik (Khusus Superadmin / Pemilik Toko) -->
            <div id="modal_koreksi_superadmin" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden border border-zcBrd">
                    <div class="px-6 py-4 border-b border-zcBrd flex items-center justify-between bg-white">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-zcTxt">Koreksi Hitungan Fisik</h3>
                                <p class="text-[11px] text-amber-700 font-semibold">Khusus Otoritas Pemilik (Superadmin)</p>
                            </div>
                        </div>
                        <button type="button" onclick="document.getElementById('modal_koreksi_superadmin').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center transition cursor-pointer">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                    <form method="POST" class="p-6 space-y-4 text-xs">
                        <input type="hidden" name="aksi" value="koreksi_hitung_superadmin">
                        <input type="hidden" name="id_detail" id="koreksi_id_detail">

                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                            <div class="text-[10px] text-slate-400 font-semibold uppercase">Produk yang Dikoreksi:</div>
                            <div class="font-bold text-slate-900 text-xs" id="koreksi_nama_produk">-</div>
                            <div class="text-[11px] text-slate-600 font-mono" id="koreksi_batch_info">-</div>
                        </div>

                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            Fitur ini digunakan jika terjadi kekeliruan pencatatan lapangan oleh kasir atau admin. Mengubah angka fisik di sini akan memperbarui status selisih secara otomatis.
                        </p>

                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block font-bold text-emerald-800 text-[11px] mb-1">Fisik Baik:</label>
                                <input type="number" min="0" name="koreksi_fisik_baik" id="koreksi_fisik_baik" class="w-full border border-slate-300 rounded-lg p-2 text-center font-mono font-bold text-xs focus:ring-2 focus:ring-slate-900" required>
                            </div>
                            <div>
                                <label class="block font-bold text-amber-800 text-[11px] mb-1">Fisik Rusak:</label>
                                <input type="number" min="0" name="koreksi_fisik_rusak" id="koreksi_fisik_rusak" class="w-full border border-slate-300 rounded-lg p-2 text-center font-mono font-bold text-xs focus:ring-2 focus:ring-slate-900" required>
                            </div>
                            <div>
                                <label class="block font-bold text-rose-800 text-[11px] mb-1">Fisik Expired:</label>
                                <input type="number" min="0" name="koreksi_fisik_expired" id="koreksi_fisik_expired" class="w-full border border-slate-300 rounded-lg p-2 text-center font-mono font-bold text-xs focus:ring-2 focus:ring-slate-900" required>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-zcTxt mb-1">Alasan Koreksi oleh Superadmin (Wajib):</label>
                            <textarea name="alasan_koreksi" rows="2" class="w-full border border-zcBrd rounded-xl p-2.5 text-xs focus:ring-1 focus:ring-zc" placeholder="Contoh: Kesalahan salah hitung kasir di rak etalase..." required></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-zcBrd">
                            <button type="button" onclick="document.getElementById('modal_koreksi_superadmin').classList.add('hidden')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50 transition cursor-pointer">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-black text-white font-bold rounded-xl shadow-xs transition cursor-pointer">Simpan Koreksi Fisik</button>
                        </div>
                    </form>
                </div>
            </div>

            """

if modal_target in content:
    content = content.replace(modal_target, modal_koreksi_html + modal_target)
    print("✓ Modal koreksi superadmin added successfully.")
else:
    print("! Modal target not found!")

# 6. Add JS openModalKoreksiSuperadmin
old_js_start = "function openModalRecount(idDetail, hitungLama, namaProduk) {"
new_js_code = """function openModalKoreksiSuperadmin(item) {
    document.getElementById('koreksi_id_detail').value = item.id;
    document.getElementById('koreksi_nama_produk').innerText = item.nama_produk + ' — ' + item.nama_variasi;
    document.getElementById('koreksi_batch_info').innerText = 'Batch: ' + (item.no_batch || 'SN Tracker') + ' | Satuan: ' + (item.nama_satuan || 'Unit');
    document.getElementById('koreksi_fisik_baik').value = item.fisik_baik || 0;
    document.getElementById('koreksi_fisik_rusak').value = item.fisik_rusak || 0;
    document.getElementById('koreksi_fisik_expired').value = item.fisik_expired || 0;
    document.getElementById('modal_koreksi_superadmin').classList.remove('hidden');
}

function openModalRecount(idDetail, hitungLama, namaProduk) {"""

if old_js_start in content:
    content = content.replace(old_js_start, new_js_code)
    print("✓ JS openModalKoreksiSuperadmin added successfully.")
else:
    print("! JS openModalRecount pattern not found!")

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated inventori/stok_opname.php successfully.")

