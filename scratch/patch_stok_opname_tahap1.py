# Script to patch Tahap 1 counting, filter tabs, row data attributes, and JS demo functions.

with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

# 1. Add support for sudah_hitung in WHERE clause
old_where_sql = """    } elseif ($filterSts === 'belum_hitung') {
        $where[] = "dob.hitung_fisik IS NULL";
    } elseif ($filterSts === 'sesuai') {"""

new_where_sql = """    } elseif ($filterSts === 'belum_hitung') {
        $where[] = "dob.hitung_fisik IS NULL";
    } elseif ($filterSts === 'sudah_hitung') {
        $where[] = "dob.hitung_fisik IS NOT NULL";
    } elseif ($filterSts === 'sesuai') {"""

if old_where_sql in text:
    text = text.replace(old_where_sql, new_where_sql)
    print("Added sudah_hitung WHERE clause!")
else:
    print("Warning: old_where_sql not found")

# 2. Add Completion Banner & Filter Tabs right above the table in Tahap 1
old_form_start = """                <!-- Form Bulk Simpan Seluruh Hitungan -->
                <form id="form_bulk_simpan" method="POST">
                    <input type="hidden" name="aksi" value="simpan_semua_hitung_fisik">"""

new_form_start = """                <?php if ($stats['belum_hitung'] === 0 && $isKasir): ?>
                    <!-- Banner Sukses Pencacahan Fisik Rak Kasir Selesai -->
                    <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-200 text-center space-y-2.5 shadow-2xs">
                        <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center mx-auto shadow-sm">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-emerald-950">Pencacahan Fisik Rak Selesai (100% Terdata)</h3>
                        <p class="text-xs text-emerald-800 max-w-xl mx-auto leading-relaxed">
                            Seluruh <?= $stats['total'] ?> entitas barang fisik telah berhasil Anda input ke server.
                            Saat ini kasir POS <strong>masih dibekukan sementara</strong> demi integritas persediaan sampai Superadmin/Admin menyelesaikan verifikasi selisih dan membuka kunci server.
                        </p>
                        <div class="pt-1 flex items-center justify-center gap-3">
                            <a href="stok_opname.php" class="px-3.5 py-1.5 rounded-xl bg-white border border-emerald-300 hover:bg-emerald-100 text-emerald-900 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                <span>Periksa Status Pembukaan Kunci POS</span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tab Pemfilteran Progres Hitung Kasir (Belum vs Sudah Dihitung) -->
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
                    </div>
                    <div class="text-[11px] text-slate-500 hidden sm:block">
                        <?php if ($filterSts === 'belum_hitung'): ?>
                            <span>Menampilkan barang yang belum diinput. Barang yang disimpan akan otomatis pindah ke tab 'Sudah Dihitung'.</span>
                        <?php elseif ($filterSts === 'sudah_hitung'): ?>
                            <span>Menampilkan barang yang sudah tersimpan. Anda dapat merevisi angka jika terdapat koreksi.</span>
                        <?php else: ?>
                            <span>Menampilkan seluruh <?= $stats['total'] ?> baris persediaan.</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Form Bulk Simpan Seluruh Hitungan -->
                <form id="form_bulk_simpan" method="POST">
                    <input type="hidden" name="aksi" value="simpan_semua_hitung_fisik">"""

if old_form_start in text:
    text = text.replace(old_form_start, new_form_start)
    print("Added completion banner and filter tabs!")
else:
    print("Warning: old_form_start not found")

# 3. Add data-* attributes to row <tr>
old_tr_row = """                                        <tr class="hover:bg-slate-50/70 transition" data-barcode-search="<?= $searchableStr ?>">"""

new_tr_row = """                                        <tr class="hover:bg-slate-50/70 transition" data-barcode-search="<?= $searchableStr ?>" data-id="<?= $it['id'] ?>" data-nama="<?= htmlspecialchars($it['nama_produk']) ?>" data-satuan-tipe="<?= $it['satuan_tipe'] ?>" data-stok-sistem="<?= $it['stok_sistem_fisik'] ?>" data-is-exp="<?= $isObatExp ? '1' : '0' ?>" data-komoditas="<?= $it['jenis_komoditas'] ?>">"""

if old_tr_row in text:
    text = text.replace(old_tr_row, new_tr_row)
    print("Added data-* attributes to table rows!")
else:
    print("Warning: old_tr_row not found")

# 4. Add JS functions isiOtomatisAngkaDemo() and submitBulkCountingForm()
old_js_end = """document.getElementById('barcode_scan_input')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        handleBarcodeScan();
    }
});
</script>"""

new_js_end = """document.getElementById('barcode_scan_input')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        handleBarcodeScan();
    }
});

// Fungsi Cepat Pengisian Angka Fisik Demo / Uji Coba Sesuai 5 Skenario
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
}

// Fungsi Submit Form Bulk Simpan dengan Validasi Input
function submitBulkCountingForm() {
    let form = document.getElementById('form_bulk_simpan');
    if (!form) return;

    let inputs = form.querySelectorAll('input[type="number"]');
    let hasValue = false;
    inputs.forEach(inp => {
        if (inp.value !== '' && inp.value !== null) {
            hasValue = true;
        }
    });

    if (!hasValue) {
        alert('Perhatian: Anda belum memasukkan angka hitungan fisik pada baris mana pun.\\n\\nSilakan isi kuantitas fisik rak terlebih dahulu, atau gunakan tombol "Isi Cepat Angka Uji Coba" untuk demo.');
        return;
    }

    if (confirm('Simpan seluruh hasil hitung fisik yang telah diinput ke server?')) {
        form.submit();
    }
}
</script>"""

if old_js_end in text:
    text = text.replace(old_js_end, new_js_end)
    print("Added JS functions isiOtomatisAngkaDemo() and submitBulkCountingForm()!")
else:
    print("Warning: old_js_end not found")

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(text)

print("Patch Tahap 1 complete.")

