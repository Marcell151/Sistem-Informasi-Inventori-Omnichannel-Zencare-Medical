import re

with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Kasir layout container full-width
old_kasir_block = """if ($isKasir) {
    // KHUSUS KASIR: Tampilan Layar Penuh Tanpa Sidebar (Selaras dengan Terminal POS)
    ?>
    <div class="min-h-screen bg-slate-50 flex flex-col">
        <!-- Header Khusus Terminal Kasir (Identik dengan Terminal POS) -->
        <header class="bg-white border-b border-slate-200 px-6 py-3.5 sticky top-0 z-30 shadow-2xs">
            <div class="max-w-7xl mx-auto flex items-center justify-between">"""

new_kasir_block = """if ($isKasir) {
    // KHUSUS KASIR: Tampilan Layar Penuh Tanpa Sidebar (Selaras dengan Terminal POS)
    ?>
    <div class="w-full flex-1 min-h-screen bg-slate-50 flex flex-col min-w-0">
        <!-- Header Khusus Terminal Kasir (Identik dengan Terminal POS) -->
        <header class="w-full bg-white border-b border-slate-200 px-6 py-3.5 sticky top-0 z-30 shadow-2xs shrink-0">
            <div class="w-full max-w-7xl mx-auto flex items-center justify-between">"""

assert old_kasir_block in content, "old_kasir_block not found"
content = content.replace(old_kasir_block, new_kasir_block)

# 1b. Main tag and container width
old_main_block = """        <main class="flex-1 p-6">
    <?php
} else {
    layoutSidebar('stok_opname');
    layoutHeader('Stok Opname Persediaan', 'Pencacahan fisik stok (Blind Count), rekonsiliasi anomali, dan berita acara penyesuaian');
}
?>

<div class="space-y-5 max-w-7xl mx-auto">"""

new_main_block = """        <main class="flex-1 w-full px-4 sm:px-6 py-5">
    <?php
} else {
    layoutSidebar('stok_opname');
    layoutHeader('Stok Opname Persediaan', 'Pencacahan fisik stok (Blind Count), rekonsiliasi anomali, dan berita acara penyesuaian');
}
?>

<div class="space-y-5 max-w-7xl w-full mx-auto">"""

assert old_main_block in content, "old_main_block not found"
content = content.replace(old_main_block, new_main_block)

# 2. Backend bulk save robustness: skip only if all 3 are empty
old_backend_loop = """                    foreach ($bulkBaik as $idDet => $valBaik) {
                        if ($valBaik === '' || $valBaik === null) continue;
                        $idDet = intval($idDet);
                        $fBaik = max(0, intval($valBaik));
                        $fRusak = max(0, intval($bulkRusak[$idDet] ?? 0));
                        $fExp   = max(0, intval($bulkExpired[$idDet] ?? 0));"""

new_backend_loop = """                    foreach ($bulkBaik as $idDet => $valBaik) {
                        $idDet = intval($idDet);
                        $vBaik = $bulkBaik[$idDet] ?? '';
                        $vRusak = $bulkRusak[$idDet] ?? '';
                        $vExp = $bulkExpired[$idDet] ?? '';
                        if ($vBaik === '' && $vRusak === '' && $vExp === '') continue;
                        $fBaik = ($vBaik !== '') ? max(0, intval($vBaik)) : 0;
                        $fRusak = ($vRusak !== '') ? max(0, intval($vRusak)) : 0;
                        $fExp   = ($vExp !== '') ? max(0, intval($vExp)) : 0;"""

assert old_backend_loop in content, "old_backend_loop not found"
content = content.replace(old_backend_loop, new_backend_loop)

# 3. Active session header: remove candy bubbles and red locked pill
old_active_header = """                    <div class="flex items-center gap-3">
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
                    </div>"""

new_active_header = """                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5 flex-wrap text-xs text-slate-600">
                                <span class="font-bold text-slate-900 font-mono tracking-tight text-sm">
                                    Sesi: <?= htmlspecialchars($activeSession['kode_sesi']) ?>
                                </span>
                                <span class="text-slate-300">|</span>
                                <span class="font-semibold text-slate-800">
                                    <?= $currentTahap === 'Penghitungan' ? 'Tahap 1: Penghitungan Fisik (Blind Count)' : 'Tahap 2: Verifikasi &amp; Rekonsiliasi Selisih' ?>
                                </span>
                                <span class="text-slate-300">|</span>
                                <span class="text-slate-500">
                                    Dimulai: <?= date('d/m/Y H:i', strtotime($activeSession['dibekukan_pada'])) ?> WIB
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Status Server: Transaksi kasir POS, checkout toko daring, konversi UOM, dan penerimaan logistik sedang dibekukan sementara.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span>Transaksi Terkunci (423)</span>
                        </span>
                        <?php if ($isSuperadmin): ?>
                            <button type="button" onclick="document.getElementById('modal_batal_sesi').classList.remove('hidden')" class="text-[11px] text-slate-400 hover:text-rose-600 font-semibold px-2 py-1 rounded transition cursor-pointer" title="Jalur Darurat jika sesi perlu dibatalkan">
                                Batalkan Sesi
                            </button>
                        <?php endif; ?>
                    </div>"""

assert old_active_header in content, "old_active_header not found"
content = content.replace(old_active_header, new_active_header)

# 4. Buttons: neutralize Isi Cepat Angka Uji Coba and rename Simpan Semua Hitungan Fisik -> Simpan Hasil Hitung
old_buttons = """                                <!-- 2. Tombol Cepat Isi Angka Demo (Client-Side JS) -->
                                <button type="button" onclick="isiOtomatisAngkaDemo()" class="h-9 px-3.5 rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-900 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Pintasan Uji Coba: Isi otomatis angka fisik di tabel sesuai skenario demo pengujian">
                                    <svg class="w-3.5 h-3.5 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                    <span>Isi Cepat Angka Uji Coba</span>
                                </button>

                                <!-- 3. Tombol Simpan Semua Hitungan Fisik -->
                                <button type="button" onclick="submitBulkCountingForm()" id="btn_bulk_simpan" class="h-9 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer" title="Simpan seluruh hasil hitung fisik yang tertera di tabel sekaligus">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                    <span>Simpan Semua Hitungan Fisik</span>
                                </button>"""

new_buttons = """                                <!-- 2. Tombol Cepat Isi Angka Demo (Client-Side JS) -->
                                <button type="button" onclick="isiOtomatisAngkaDemo()" class="h-9 px-3.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Pintasan Uji Coba: Isi otomatis angka fisik di tabel sesuai skenario demo pengujian">
                                    <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                    <span>Isi Cepat Angka Uji Coba</span>
                                </button>

                                <!-- 3. Tombol Simpan Hasil Hitung -->
                                <button type="button" onclick="submitBulkCountingForm()" id="btn_bulk_simpan" class="h-9 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer" title="Simpan hasil hitung fisik yang telah diinput ke server">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                    <span>Simpan Hasil Hitung</span>
                                </button>"""

assert old_buttons in content, "old_buttons not found"
content = content.replace(old_buttons, new_buttons)

# 5. Stat cards: Neutral typography, and if Kasir only show 3 cards
old_stats = """            <!-- Ringkasan Metrik Dashboard Opname -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                    <span class="text-[10px] text-zcMut font-bold uppercase tracking-wider block">Total Baris</span>
                    <span class="text-xl font-black text-zcTxt mt-1 block"><?= $stats['total'] ?></span>
                </div>
                <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                    <span class="text-[10px] text-amber-700 font-bold uppercase tracking-wider block">Belum Dihitung</span>
                    <span class="text-xl font-black text-amber-600 mt-1 block"><?= $stats['belum_hitung'] ?></span>
                </div>
                <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                    <span class="text-[10px] text-emerald-700 font-bold uppercase tracking-wider block">Sudah Dihitung</span>
                    <span class="text-xl font-black text-emerald-600 mt-1 block"><?= $stats['sudah_hitung'] ?></span>
                </div>
                <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                    <span class="text-[10px] text-blue-700 font-bold uppercase tracking-wider block">Sesuai (Selisih 0)</span>
                    <span class="text-xl font-black text-blue-600 mt-1 block"><?= $stats['sesuai'] ?></span>
                </div>
                <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                    <span class="text-[10px] text-rose-700 font-bold uppercase tracking-wider block">Ada Selisih</span>
                    <span class="text-xl font-black text-rose-600 mt-1 block"><?= $stats['berselisih'] ?></span>
                </div>
                <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                    <span class="text-[10px] text-slate-600 font-bold uppercase tracking-wider block">Sudah Dimutasi</span>
                    <span class="text-xl font-black text-slate-800 mt-1 block"><?= $stats['sudah_dimutasi'] ?> / <?= $stats['berselisih'] ?></span>
                </div>
            </div>"""

new_stats = """            <!-- Ringkasan Metrik Dashboard Opname -->
            <?php if ($isKasir): ?>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Total Baris Barang</span>
                        <span class="text-xl font-bold font-mono text-slate-900 mt-1 block"><?= $stats['total'] ?></span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Belum Dihitung</span>
                        <span class="text-xl font-bold font-mono text-slate-900 mt-1 block"><?= $stats['belum_hitung'] ?></span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Sudah Dihitung</span>
                        <span class="text-xl font-bold font-mono text-slate-900 mt-1 block"><?= $stats['sudah_hitung'] ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                    <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Total Baris</span>
                        <span class="text-xl font-bold font-mono text-slate-900 mt-1 block"><?= $stats['total'] ?></span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Belum Dihitung</span>
                        <span class="text-xl font-bold font-mono text-slate-900 mt-1 block"><?= $stats['belum_hitung'] ?></span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Sudah Dihitung</span>
                        <span class="text-xl font-bold font-mono text-slate-900 mt-1 block"><?= $stats['sudah_hitung'] ?></span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Sesuai (Klop)</span>
                        <span class="text-xl font-bold font-mono text-slate-900 mt-1 block"><?= $stats['sesuai'] ?></span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Ada Selisih</span>
                        <span class="text-xl font-bold font-mono text-slate-900 mt-1 block"><?= $stats['berselisih'] ?></span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Sudah Dimutasi</span>
                        <span class="text-xl font-bold font-mono text-slate-900 mt-1 block"><?= $stats['sudah_dimutasi'] ?> / <?= $stats['berselisih'] ?></span>
                    </div>
                </div>
            <?php endif; ?>"""

assert old_stats in content, "old_stats not found"
content = content.replace(old_stats, new_stats)

# 6. Blind count banner: neutral styling
old_panduan = """                <!-- Banner Petunjuk Kasir (Blind Count) -->
                <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 text-amber-950 text-xs shadow-2xs flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-white border border-amber-300 text-amber-800 flex items-center justify-center shrink-0 font-bold">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    </div>
                    <div class="leading-relaxed">
                        <strong class="font-bold text-amber-950 block">Panduan Pencacahan Fisik (Blind Count Multi-Kondisi)</strong>
                        Periksa fisik barang di rak etalase secara independen. Masukkan jumlah fisik ke dalam 3 kolom kondisi:
                        <strong>Kondisi Baik</strong> (layak jual), <strong>Kondisi Rusak</strong> (cacat kemasan/botol pecah), dan <strong>Kondisi Expired</strong> (melewati tanggal kedaluwarsa). Sesuai standar audit, angka sistem dan selisih dirahasiakan selama tahap penghitungan.
                    </div>
                </div>"""

new_panduan = """                <!-- Panduan Pencacahan Fisik (Blind Count) -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs flex items-start gap-3">
                    <div class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-600 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    </div>
                    <div class="leading-relaxed">
                        <strong class="font-bold text-slate-900 block">Panduan Pencacahan Fisik (Blind Count)</strong>
                        Periksa fisik barang di rak etalase secara independen. Masukkan kuantitas fisik pada 3 kolom:
                        <strong>Kondisi Baik</strong> (layak jual), <strong>Kondisi Rusak</strong> (cacat fisik), dan <strong>Kondisi Expired</strong> (kedaluwarsa). Angka pembukuan sistem dirahasiakan selama tahap pencacahan fisik.
                    </div>
                </div>"""

assert old_panduan in content, "old_panduan not found"
content = content.replace(old_panduan, new_panduan)

# 7. Tabs: Neutral monochrome styling
old_tabs = """                <!-- Tab Pemfilteran Progres Hitung Kasir (Belum vs Sudah Dihitung) -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pt-1">
                    <div class="flex items-center gap-1.5">
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=belum_hitung&q=<?= urlencode($searchQ) ?>" class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?= $filterSts === 'belum_hitung' ? 'bg-amber-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                            <span>Belum Dihitung</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono <?= $filterSts === 'belum_hitung' ? 'bg-amber-800 text-white' : 'bg-slate-200 text-slate-700' ?>"><?= $stats['belum_hitung'] ?></span>
                        </a>
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=sudah_hitung&q=<?= urlencode($searchQ) ?>" class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?= $filterSts === 'sudah_hitung' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                            <span>Sudah Dihitung (Tersimpan)</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono <?= $filterSts === 'sudah_hitung' ? 'bg-emerald-800 text-white' : 'bg-slate-200 text-slate-700' ?>"><?= $stats['sudah_hitung'] ?></span>
                        </a>
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=semua&q=<?= urlencode($searchQ) ?>" class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?= $filterSts === 'semua' ? 'bg-slate-900 text-white shadow-2xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                            <span>Semua Item</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono <?= $filterSts === 'semua' ? 'bg-slate-700 text-white' : 'bg-slate-200 text-slate-700' ?>"><?= $stats['total'] ?></span>
                        </a>
                    </div>"""

new_tabs = """                <!-- Tab Pemfilteran Progres Hitung Kasir (Belum vs Sudah Dihitung) -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pt-1">
                    <div class="flex items-center gap-1.5">
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=belum_hitung&q=<?= urlencode($searchQ) ?>" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 <?= $filterSts === 'belum_hitung' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                            <span>Belum Dihitung</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono <?= $filterSts === 'belum_hitung' ? 'bg-slate-800 text-white' : 'bg-slate-200 text-slate-700' ?>"><?= $stats['belum_hitung'] ?></span>
                        </a>
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=sudah_hitung&q=<?= urlencode($searchQ) ?>" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 <?= $filterSts === 'sudah_hitung' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                            <span>Sudah Dihitung (Tersimpan)</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono <?= $filterSts === 'sudah_hitung' ? 'bg-slate-800 text-white' : 'bg-slate-200 text-slate-700' ?>"><?= $stats['sudah_hitung'] ?></span>
                        </a>
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=semua&q=<?= urlencode($searchQ) ?>" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 <?= $filterSts === 'semua' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                            <span>Semua Item</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono <?= $filterSts === 'semua' ? 'bg-slate-800 text-white' : 'bg-slate-200 text-slate-700' ?>"><?= $stats['total'] ?></span>
                        </a>
                    </div>"""

assert old_tabs in content, "old_tabs not found"
content = content.replace(old_tabs, new_tabs)

# 8. Table header & Input fields
old_th = """                                        <th class="py-3 px-3 text-center w-28 bg-emerald-50/50 border-x border-emerald-100">
                                            <div class="text-emerald-900 font-bold">KONDISI BAIK</div>
                                            <div class="text-[9px] text-emerald-700">(Layak Jual)</div>
                                        </th>
                                        <th class="py-3 px-3 text-center w-28 bg-amber-50/50 border-r border-amber-100">
                                            <div class="text-amber-900 font-bold">KONDISI RUSAK</div>
                                            <div class="text-[9px] text-amber-700">(Cacat Fisik)</div>
                                        </th>
                                        <th class="py-3 px-3 text-center w-28 bg-rose-50/50 border-r border-rose-100">
                                            <div class="text-rose-900 font-bold">KONDISI EXPIRED</div>
                                            <div class="text-[9px] text-rose-700">(Kedaluwarsa)</div>
                                        </th>"""

new_th = """                                        <th class="py-3 px-3 text-center w-28 border-x border-slate-200 bg-slate-50 text-slate-700">
                                            <div class="font-bold">Kondisi Baik</div>
                                            <div class="text-[9px] text-slate-500 font-normal">Layak Jual</div>
                                        </th>
                                        <th class="py-3 px-3 text-center w-28 border-r border-slate-200 bg-slate-50 text-slate-700">
                                            <div class="font-bold">Kondisi Rusak</div>
                                            <div class="text-[9px] text-slate-500 font-normal">Cacat Fisik</div>
                                        </th>
                                        <th class="py-3 px-3 text-center w-28 border-r border-slate-200 bg-slate-50 text-slate-700">
                                            <div class="font-bold">Kondisi Expired</div>
                                            <div class="text-[9px] text-slate-500 font-normal">Kedaluwarsa</div>
                                        </th>"""

assert old_th in content, "old_th not found"
content = content.replace(old_th, new_th)

old_inputs = """                                            <!-- INPUT FISIK BAIK -->
                                            <td class="py-2.5 px-3 text-center bg-emerald-50/20 border-x border-emerald-100">
                                                <?php if ($isObatExp): ?>
                                                    <input type="hidden" name="bulk_fisik_baik[<?= $it['id'] ?>]" value="0">
                                                    <span class="text-[10px] font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200 block">Kunci (0) Expired</span>
                                                <?php else: ?>
                                                    <input type="number" 
                                                           name="bulk_fisik_baik[<?= $it['id'] ?>]" 
                                                           id="input_baik_<?= $it['id'] ?>"
                                                           value="<?= $it['fisik_baik'] !== null ? $it['fisik_baik'] : '' ?>"
                                                           min="0" 
                                                           placeholder="0"
                                                           class="w-16 border border-emerald-300 rounded-lg py-1 px-1.5 text-center font-mono font-bold text-xs focus:outline-none focus:border-emerald-600 bg-white shadow-2xs">
                                                <?php endif; ?>
                                            </td>

                                            <!-- INPUT FISIK RUSAK -->
                                            <td class="py-2.5 px-3 text-center bg-amber-50/20 border-r border-amber-100">
                                                <input type="number" 
                                                       name="bulk_fisik_rusak[<?= $it['id'] ?>]" 
                                                       id="input_rusak_<?= $it['id'] ?>"
                                                       value="<?= intval($it['fisik_rusak'] ?? 0) ?>"
                                                       min="0" 
                                                       placeholder="0"
                                                       class="w-16 border border-amber-300 rounded-lg py-1 px-1.5 text-center font-mono font-bold text-xs focus:outline-none focus:border-amber-600 bg-white shadow-2xs">
                                            </td>

                                            <!-- INPUT FISIK EXPIRED -->
                                            <td class="py-2.5 px-3 text-center bg-rose-50/20 border-r border-rose-100">
                                                <?php if ($isAlkes): ?>
                                                    <input type="hidden" name="bulk_fisik_expired[<?= $it['id'] ?>]" value="0">
                                                    <span class="text-[10px] text-slate-400 font-mono">-</span>
                                                <?php else: ?>
                                                    <input type="number" 
                                                           name="bulk_fisik_expired[<?= $it['id'] ?>]" 
                                                           id="input_exp_<?= $it['id'] ?>"
                                                           value="<?= intval($it['fisik_expired'] ?? 0) ?>"
                                                           min="0" 
                                                           placeholder="0"
                                                           class="w-16 border <?= $isObatExp ? 'border-rose-400 bg-rose-50/40 text-rose-900 font-black' : 'border-rose-300 bg-white' ?> rounded-lg py-1 px-1.5 text-center font-mono font-bold text-xs focus:outline-none focus:border-rose-600 shadow-2xs">
                                                <?php endif; ?>
                                            </td>"""

new_inputs = """                                            <!-- INPUT FISIK BAIK -->
                                            <td class="py-2.5 px-3 text-center border-x border-slate-100">
                                                <?php if ($isObatExp): ?>
                                                    <input type="hidden" name="bulk_fisik_baik[<?= $it['id'] ?>]" value="0">
                                                    <span class="text-[10px] font-semibold text-slate-400 bg-slate-50 px-2 py-0.5 rounded border border-slate-200 block">Terkunci (0) Exp</span>
                                                <?php else: ?>
                                                    <input type="number" 
                                                           name="bulk_fisik_baik[<?= $it['id'] ?>]" 
                                                           id="input_baik_<?= $it['id'] ?>"
                                                           value="<?= $it['fisik_baik'] !== null ? $it['fisik_baik'] : '' ?>"
                                                           min="0" 
                                                           placeholder="0"
                                                           class="w-16 border border-slate-300 rounded-lg py-1 px-1.5 text-center font-mono text-xs focus:outline-none focus:border-slate-800 bg-white shadow-2xs">
                                                <?php endif; ?>
                                            </td>

                                            <!-- INPUT FISIK RUSAK -->
                                            <td class="py-2.5 px-3 text-center border-r border-slate-100">
                                                <input type="number" 
                                                       name="bulk_fisik_rusak[<?= $it['id'] ?>]" 
                                                       id="input_rusak_<?= $it['id'] ?>"
                                                       value="<?= $it['fisik_rusak'] !== null ? $it['fisik_rusak'] : '' ?>"
                                                       min="0" 
                                                       placeholder="0"
                                                       class="w-16 border border-slate-300 rounded-lg py-1 px-1.5 text-center font-mono text-xs focus:outline-none focus:border-slate-800 bg-white shadow-2xs">
                                            </td>

                                            <!-- INPUT FISIK EXPIRED -->
                                            <td class="py-2.5 px-3 text-center border-r border-slate-100">
                                                <?php if ($isAlkes): ?>
                                                    <input type="hidden" name="bulk_fisik_expired[<?= $it['id'] ?>]" value="0">
                                                    <span class="text-[10px] text-slate-400 font-mono">-</span>
                                                <?php else: ?>
                                                    <input type="number" 
                                                           name="bulk_fisik_expired[<?= $it['id'] ?>]" 
                                                           id="input_exp_<?= $it['id'] ?>"
                                                           value="<?= $it['fisik_expired'] !== null ? $it['fisik_expired'] : '' ?>"
                                                           min="0" 
                                                           placeholder="0"
                                                           class="w-16 border border-slate-300 bg-white rounded-lg py-1 px-1.5 text-center font-mono text-xs focus:outline-none focus:border-slate-800 shadow-2xs">
                                                <?php endif; ?>
                                            </td>"""

assert old_inputs in content, "old_inputs not found"
content = content.replace(old_inputs, new_inputs)

# 9. isiOtomatisAngkaDemo() JS function
old_js_demo = """// Fungsi Cepat Pengisian Angka Fisik Demo / Uji Coba Sesuai 5 Skenario
function isiOtomatisAngkaDemo() {
    let rows = document.querySelectorAll('tr[data-id]');
    let countFilled = 0;
    rows.forEach(tr => {
        let id = tr.getAttribute('data-id');
        let nama = (tr.getAttribute('data-nama') || '').toLowerCase();
        let satuanTipe = tr.getAttribute('data-satuan-tipe') || 'kecil';
        let sistem = parseInt(tr.getAttribute('data-stok-sistem') || '0', 10);
        let isExp = tr.getAttribute('data-is-exp') === '1';

        let inBaik = document.getElementById('input_baik_' + id);
        let inRusak = document.getElementById('input_rusak_' + id);
        let inExp = document.getElementById('input_exp_' + id);

        if (!inBaik && !inRusak && !inExp) return;

        // 1. Cefadroxil: Sistem 10 (Dus) -> Baik 5, Rusak 3, Exp 2
        if (nama.includes('cefadroxil') && satuanTipe === 'besar') {
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = 5;
            if (inRusak) inRusak.value = 3;
            if (inExp) inExp.value = 2;
        }
        // 2. Sanmol Sirup (Expired): Sistem 15 -> Baik 0 (locked), Rusak 0, Exp 12 (Hilang 3)
        else if (nama.includes('sanmol')) {
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = 0;
            if (inRusak) inRusak.value = 0;
            if (inExp) inExp.value = 12;
        }
        // 3. Amoxicillin: Sistem 29 (Dus) -> Baik 23, Rusak 0, Exp 0 (Hilang 6 Dus)
        else if (nama.includes('amoxicillin')) {
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = Math.max(0, sistem - 6);
            if (inRusak) inRusak.value = 0;
            if (inExp) inExp.value = 0;
        }
        // 4. Betadine: Sistem 18 -> Baik 20, Rusak 0, Exp 0 (Surplus +2 Botol)
        else if (nama.includes('betadine')) {
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = sistem + 2;
            if (inRusak) inRusak.value = 0;
            if (inExp) inExp.value = 0;
        }
        // 5. Tensimeter Omron (Alkes SN): Sistem 1 -> Baik 0 (Hilang 1 Unit)
        else if (nama.includes('omron') || nama.includes('tensimeter')) {
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = 0;
            if (inRusak) inRusak.value = 0;
            if (inExp) inExp.value = 0;
        }
        // 116 Item Normal Lainnya: Sesuai Persis dengan Kuantitas Sistem (Klop)
        else {
            if (isExp) {
                if (inBaik && inBaik.type !== 'hidden') inBaik.value = 0;
                if (inRusak) inRusak.value = 0;
                if (inExp) inExp.value = sistem;
            } else {
                if (inBaik && inBaik.type !== 'hidden') inBaik.value = sistem;
                if (inRusak) inRusak.value = 0;
                if (inExp) inExp.value = 0;
            }
        }
        countFilled++;
    });

    alert('Berhasil mengisikan ' + countFilled + ' angka pencacahan fisik ke formulir (termasuk 5 kasus uji anomali: Cefadroxil rusak/exp, Sanmol expired, Amoxicillin hilang, Omron hilang, Betadine surplus).\\n\\nSilakan periksa angka di tabel, lalu tekan tombol "Simpan Semua Hitungan Fisik" untuk mengirim data ke server.');
}"""

new_js_demo = """// Fungsi Cepat Pengisian Angka Fisik Demo / Uji Coba Sesuai 5 Skenario
function isiOtomatisAngkaDemo() {
    let rows = document.querySelectorAll('tr[data-id]');
    let countFilled = 0;
    let anomaliCount = 0;

    let flagCefadroxil = false;
    let flagSanmol = false;
    let flagAmox = false;
    let flagOmron = false;
    let flagBetadine = false;

    rows.forEach(tr => {
        let id = tr.getAttribute('data-id');
        let nama = (tr.getAttribute('data-nama') || '').toLowerCase();
        let barcodeSearch = (tr.getAttribute('data-barcode-search') || '').toLowerCase();
        let satuanTipe = tr.getAttribute('data-satuan-tipe') || 'kecil';
        let sistem = parseInt(tr.getAttribute('data-stok-sistem') || '0', 10);
        let isExp = tr.getAttribute('data-is-exp') === '1';

        let inBaik = document.getElementById('input_baik_' + id);
        let inRusak = document.getElementById('input_rusak_' + id);
        let inExp = document.getElementById('input_exp_' + id);

        if (!inBaik && !inRusak && !inExp) return;

        // 1. Cefadroxil 500mg Batch CFD-500-26 (Rusak 2 Strip):
        if (!flagCefadroxil && nama.includes('cefadroxil') && (barcodeSearch.includes('cfd-500-26') || satuanTipe === 'kecil')) {
            flagCefadroxil = true;
            anomaliCount++;
            let rusakQty = Math.min(2, sistem);
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = Math.max(0, sistem - rusakQty);
            if (inRusak) inRusak.value = rusakQty;
            if (inExp) inExp.value = 0;
        }
        // 2. Sanmol Sirup Anak 60ml (Expired Batch SNM-24K061):
        else if (!flagSanmol && nama.includes('sanmol') && isExp) {
            flagSanmol = true;
            anomaliCount++;
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = 0;
            if (inRusak) inRusak.value = 0;
            if (inExp) inExp.value = sistem;
        }
        // 3. Amoxicillin 500mg Batch AMX-2026-01 (Hilang/Kurang 2):
        else if (!flagAmox && nama.includes('amoxicillin') && satuanTipe === 'kecil') {
            flagAmox = true;
            anomaliCount++;
            let hilangQty = Math.min(2, sistem);
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = Math.max(0, sistem - hilangQty);
            if (inRusak) inRusak.value = 0;
            if (inExp) inExp.value = 0;
        }
        // 4. Tensimeter Digital Omron (Hilang 1 Unit):
        else if (!flagOmron && (nama.includes('omron') || nama.includes('tensimeter')) && sistem >= 1) {
            flagOmron = true;
            anomaliCount++;
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = Math.max(0, sistem - 1);
            if (inRusak) inRusak.value = 0;
            if (inExp) inExp.value = 0;
        }
        // 5. Betadine Solution 15ml (Surplus +2 Botol):
        else if (!flagBetadine && nama.includes('betadine') && satuanTipe === 'kecil') {
            flagBetadine = true;
            anomaliCount++;
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = sistem + 2;
            if (inRusak) inRusak.value = 0;
            if (inExp) inExp.value = 0;
        }
        // 106 Item Normal Lainnya: Sesuai Persis dengan Kuantitas Sistem (Klop)
        else {
            if (isExp) {
                if (inBaik && inBaik.type !== 'hidden') inBaik.value = 0;
                if (inRusak) inRusak.value = 0;
                if (inExp) inExp.value = sistem;
            } else {
                if (inBaik && inBaik.type !== 'hidden') inBaik.value = sistem;
                if (inRusak) inRusak.value = 0;
                if (inExp) inExp.value = 0;
            }
        }
        countFilled++;
    });

    alert('Berhasil mengisi ' + countFilled + ' baris hitungan fisik di layar (termasuk 5 kasus anomali: Cefadroxil rusak, Sanmol expired, Amoxicillin kurang, Omron kurang, Betadine surplus).\\n\\nSilakan periksa angka di tabel, lalu tekan tombol "Simpan Hasil Hitung" untuk menyimpan data ke server.');
}"""

assert old_js_demo in content, "old_js_demo not found"
content = content.replace(old_js_demo, new_js_demo)

# Also check submitBulkCountingForm alert text if it mentions "Simpan Semua"
content = content.replace('Simpan seluruh hasil hitung fisik yang telah diinput ke server?', 'Simpan hasil hitung fisik yang telah diinput ke server?')

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("SUCCESS: inventori/stok_opname.php successfully updated!")

