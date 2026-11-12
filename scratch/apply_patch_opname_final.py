with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

# 1. Update TR in Kasir view: remove data-stok-sistem
old_tr_pattern = 'data-satuan-tipe="<?= $it[\'satuan_tipe\'] ?>" data-stok-sistem="<?= $it[\'stok_sistem_fisik\'] ?>" data-is-exp="<?= $isObatExp ? \'1\' : \'0\' ?>"'
new_tr_pattern = 'data-satuan-tipe="<?= $it[\'satuan_tipe\'] ?>" <?= $isSuperadmin ? \'data-demo-fisik="\' . $it[\'stok_sistem_fisik\'] . \'"\' : \'\' ?> data-is-exp="<?= $isObatExp ? \'1\' : \'0\' ?>"'
text = text.replace(old_tr_pattern, new_tr_pattern)

# 2. Update bulk_fisik_expired for non-expired batch
old_exp_input = """                                            <td class="py-3 px-3 text-center">
                                                <input type="number" 
                                                           name="bulk_fisik_expired[<?= $it['id'] ?>]" 
                                                           id="input_exp_<?= $it['id'] ?>"
                                                           value="<?= ($isCalculated && $it['fisik_expired'] !== null) ? $it['fisik_expired'] : '' ?>"
                                                           min="0" 
                                                           placeholder="0"
                                                           class="w-16 border border-rose-300 bg-rose-50/50 rounded-lg py-1 px-1.5 text-center text-xs font-mono font-bold text-rose-950 focus:ring-1 focus:ring-rose-500">
                                            </td>"""

new_exp_input = """                                            <td class="py-3 px-3 text-center">
                                                <?php if (!$isObatExp): ?>
                                                    <!-- Non-Expired / Alkes: Kolom Expired dinonaktifkan -->
                                                    <input type="hidden" name="bulk_fisik_expired[<?= $it['id'] ?>]" value="0" id="input_exp_<?= $it['id'] ?>">
                                                    <span class="text-[10px] font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded border border-slate-200" title="Batch belum kedaluwarsa. Kerusakan dicatat pada kolom Rusak.">-</span>
                                                <?php else: ?>
                                                    <input type="number" 
                                                           name="bulk_fisik_expired[<?= $it['id'] ?>]" 
                                                           id="input_exp_<?= $it['id'] ?>"
                                                           value="<?= ($isCalculated && $it['fisik_expired'] !== null) ? $it['fisik_expired'] : '' ?>"
                                                           min="0" 
                                                           placeholder="0"
                                                           class="w-16 border border-rose-300 bg-rose-50/50 rounded-lg py-1 px-1.5 text-center text-xs font-mono font-bold text-rose-950 focus:ring-1 focus:ring-rose-500">
                                                <?php endif; ?>
                                            </td>"""
text = text.replace(old_exp_input, new_exp_input)

# 3. Update isiOtomatisAngkaDemo to use data-demo-fisik and remove 100%
text = text.replace(
    "let sistem = parseInt(tr.getAttribute('data-stok-sistem') || '0', 10);",
    "let sistem = parseInt(tr.getAttribute('data-demo-fisik') || '0', 10);"
)
text = text.replace("• 115 Item lainnya: 100% Klop / Sesuai.", "• 105 Item lainnya: Klop / Sesuai.")

# 4. Update Simulation button to "Simulasi Otomatis" and add confirmation dialog
text = text.replace('<span>Simulasi Uji Coba</span>', '<span>Simulasi Otomatis</span>')
text = text.replace('title="Khusus keperluan demo pengujian: jalankan simulasi otomatis siklus penuh opname dalam 1 kali klik"', 'title="Simulasi Otomatis: memulihkan data awal lalu menjalankan siklus opname penuh"')
text = text.replace(
    '<form method="POST">',
    '<form method="POST" onsubmit="return confirm(\'Perhatian: Simulasi otomatis akan memulihkan data awal terlebih dahulu, lalu menjalankan siklus pemeriksaan fisik hingga penerbitan Berita Acara. Lanjutkan?\');">'
)

# 5. Check forbidden words
forbidden = ['farmasi', 'apotek', 'hpp', 'akuntansi', 'audit', 'dosen', 'bab 4', 'sidang', 'sempurna']
found = {}
for w in forbidden:
    c = text.lower().count(w)
    if c > 0:
        found[w] = c
print("stok_opname.php forbidden words:", found)

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(text)
print("Updated inventori/stok_opname.php successfully.")

