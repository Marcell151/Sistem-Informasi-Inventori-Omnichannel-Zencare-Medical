with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Replace badge in Tahap 1
old_badge_1 = """                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider <?= $grp['jenis_komoditas'] === 'Obat' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' ?>">
                                                        <?= $grp['jenis_komoditas'] === 'Obat' ? 'Obat' : 'Alkes (SN)' ?>
                                                    </span>"""

new_badge_1 = """                                                    <?php if ($grp['kategori'] === 'Alat Kesehatan'): ?>
                                                        <?php if ($grp['jenis_komoditas'] === 'Alkes'): ?>
                                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                                Alkes (SN)
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-teal-100 text-teal-800 border border-teal-200">
                                                                Alkes (BMHP)
                                                            </span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">
                                                            Obat
                                                        </span>
                                                    <?php endif; ?>"""

if old_badge_1 in content:
    content = content.replace(old_badge_1, new_badge_1, 1)
    print("✓ Tahap 1 badge updated.")
else:
    print("! Tahap 1 badge not found.")

# 2. Replace badge in Tahap 2
old_badge_2 = """                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider <?= $grp['jenis_komoditas'] === 'Obat' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' ?>">
                                                    <?= $grp['jenis_komoditas'] === 'Obat' ? 'Obat' : 'Alkes (SN)' ?>
                                                </span>"""

new_badge_2 = """                                                <?php if ($grp['kategori'] === 'Alat Kesehatan'): ?>
                                                    <?php if ($grp['jenis_komoditas'] === 'Alkes'): ?>
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                            Alkes (SN)
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-teal-100 text-teal-800 border border-teal-200">
                                                            Alkes (BMHP)
                                                        </span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">
                                                        Obat
                                                    </span>
                                                <?php endif; ?>"""

if old_badge_2 in content:
    content = content.replace(old_badge_2, new_badge_2, 1)
    print("✓ Tahap 2 badge updated.")
else:
    print("! Tahap 2 badge not found.")

# 3. Update Modal Mutasi JS badge
old_modal_js = """    let isObat = (item.jenis_komoditas === 'Obat');
    document.getElementById('mutasi_komoditas_badge').innerText = isObat ? 'Obat' : 'Alat Kesehatan';
    document.getElementById('mutasi_komoditas_badge').className = isObat ? 'font-bold text-blue-700' : 'font-bold text-emerald-700';"""

new_modal_js = """    let katProduk = item.kategori || (item.jenis_komoditas === 'Obat' ? 'Obat' : 'Alat Kesehatan');
    let isAlkesSN = (item.jenis_komoditas === 'Alkes');
    let isAlkesBMHP = (katProduk === 'Alat Kesehatan' && !isAlkesSN);
    let badgeText = katProduk;
    if (isAlkesSN) badgeText += ' (Serial Number)';
    else if (isAlkesBMHP) badgeText += ' (BMHP / Batch)';
    let badgeEl = document.getElementById('mutasi_komoditas_badge');
    if (badgeEl) {
        badgeEl.innerText = badgeText;
        badgeEl.className = (katProduk === 'Obat') ? 'font-bold text-blue-700' : 'font-bold text-teal-700';
    }"""

if old_modal_js in content:
    content = content.replace(old_modal_js, new_modal_js, 1)
    print("✓ Modal JS badge updated.")
else:
    print("! Modal JS badge not found.")

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Saved inventori/stok_opname.php successfully.")

