with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update isiOtomatisAngkaDemo in stok_opname.php
old_demo = """        // 4. Tensimeter Digital Omron (Alkes SN) -> Hilang 1 Unit:
        else if (!flagOmron && nama.includes('tensimeter') && sistem >= 1) {
            flagOmron = true;
            anomaliCount++;
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = Math.max(0, sistem - 1);
            if (inRusak) inRusak.value = 0;
            if (inExp) inExp.value = 0;
            tr.classList.add('bg-rose-50/70');
        }
        // 5. Betadine Solution 15ml (Satuan Kecil) -> Surplus +2 Botol:
        else if (!flagBetadine && nama.includes('betadine') && satuanTipe === 'kecil') {
            flagBetadine = true;
            anomaliCount++;
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = sistem + 2;
            if (inRusak) inRusak.value = 0;
            if (inExp) inExp.value = 0;
            tr.classList.add('bg-blue-50/70');
        }"""

new_demo = """        // 4. Tensimeter Digital Omron (Alkes SN) -> Hilang 1 Unit:
        else if (!flagOmron && nama.includes('tensimeter') && sistem >= 1) {
            flagOmron = true;
            anomaliCount++;
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = Math.max(0, sistem - 1);
            if (inRusak) inRusak.value = 0;
            if (inExp) inExp.value = 0;
            tr.classList.add('bg-rose-50/70');
        }
        // 5. Termometer Digital Omron (Alkes SN) -> Rusak 1 Unit:
        else if (!flagTermo && nama.includes('termometer digital') && sistem >= 1) {
            flagTermo = true;
            anomaliCount++;
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = Math.max(0, sistem - 1);
            if (inRusak) inRusak.value = 1; // 1 Unit Rusak Fisik / Cacat Sensor
            if (inExp) inExp.value = 0;
            tr.classList.add('bg-amber-50/70');
        }
        // 6. Betadine Solution 15ml (Satuan Kecil) -> Surplus +2 Botol:
        else if (!flagBetadine && nama.includes('betadine') && satuanTipe === 'kecil') {
            flagBetadine = true;
            anomaliCount++;
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = sistem + 2;
            if (inRusak) inRusak.value = 0;
            if (inExp) inExp.value = 0;
            tr.classList.add('bg-blue-50/70');
        }"""

# Also declare let flagTermo = false;
content = content.replace("let flagBetadine = false;", "let flagBetadine = false;\n    let flagTermo = false;")

assert old_demo in content, "old_demo not found"
content = content.replace(old_demo, new_demo)

# Update alert message in isiOtomatisAngkaDemo
old_msg = """    let msgAnomali = 'Berhasil mengisi ' + countFilled + ' baris hitungan fisik di layar sesuai skenario pengujian:\\n' +
        '• Cefadroxil: 2 Strip Rusak (tercatat di kolom Kondisi Rusak)\\n' +
        '• Sanmol Sirup: Kedaluwarsa (tercatat di kolom Kondisi Expired)\\n' +
        '• Amoxicillin: Kurang 2 Strip (Fisik 6 dari Sistem 8)\\n' +
        '• Tensimeter Omron: Kurang 1 Unit (Fisik 7 dari Sistem 8)\\n' +
        '• Betadine Solution: Surplus +2 Botol (Fisik 14 dari Sistem 12)\\n' +
        '• 106 Item lainnya: 100% Klop / Sesuai.\\n\\n' +
        'Silakan tekan tombol "Simpan Hasil Hitung" untuk menyimpan data ke server.';"""

new_msg = """    let msgAnomali = 'Berhasil mengisi ' + countFilled + ' baris hitungan fisik di layar sesuai skenario pengujian:\\n' +
        '• Cefadroxil (Obat): 2 Strip Rusak (di kolom Kondisi Rusak)\\n' +
        '• Sanmol Sirup (Obat): Kedaluwarsa (di kolom Kondisi Expired)\\n' +
        '• Amoxicillin (Obat): Hilang/Kurang 2 Strip (Fisik 6 dari Sistem 8)\\n' +
        '• Tensimeter Omron (Alkes): Hilang 1 Unit (Fisik 7 dari Sistem 8)\\n' +
        '• Termometer Omron (Alkes): 1 Unit Rusak Fisik (di kolom Kondisi Rusak)\\n' +
        '• Betadine Solution (Obat): Surplus +2 Botol (Fisik 14 dari Sistem 12)\\n' +
        '• 115 Item lainnya: 100% Klop / Sesuai.\\n\\n' +
        'Silakan tekan tombol "Simpan Hasil Hitung" untuk menyimpan data ke server.';"""

assert old_msg in content, "old_msg not found"
content = content.replace(old_msg, new_msg)

# 2. In openModalMutasi, pre-select default reason based on whether fisik_rusak > 0
old_alkes_fetch = """                    sns.forEach(s => {
                        html += `
                            <div class="sn-item-card p-2 border border-slate-200 rounded-lg bg-white space-y-1.5 shadow-2xs">
                                <div class="flex items-center justify-between">
                                    <label class="sn-item-label flex items-center gap-2 cursor-pointer select-none">
                                        <input type="checkbox" name="selected_sn_ids[]" value="${s.id}" class="rounded border-slate-300 text-zc focus:ring-zc sn-checkbox cursor-pointer" onchange="toggleSnItemReason(this, ${s.id})">
                                        <span class="font-mono text-xs font-bold text-slate-800">${s.serial_number}</span>
                                    </label>
                                    <span class="text-[10px] text-slate-400 font-medium">Status: ${s.status}</span>
                                </div>
                                <div id="sn_reason_box_${s.id}" class="hidden pt-1.5 border-t border-slate-100 space-y-1.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-[11px] text-slate-600 font-semibold">Alasan SN ini:</span>
                                        <select name="sn_reason[${s.id}]" class="border border-slate-300 rounded px-2 py-1 text-xs font-semibold bg-white focus:ring-1 focus:ring-zc text-slate-700" onchange="toggleSnCustomInput(this, ${s.id})">
                                            <option value="Barang Rusak">Barang Rusak (Cacat Fisik / Fungsi)</option>
                                            <option value="Barang Hilang">Barang Hilang (Tidak Ditemukan)</option>
                                            <option value="Koreksi Salah Catat / Ditemukan">Koreksi Salah Catat</option>
                                            <option value="Lainnya">Alasan Lainnya (Kustom)...</option>
                                        </select>
                                    </div>"""

new_alkes_fetch = """                    let defaultSnReason = (parseInt(item.fisik_rusak) > 0) ? 'Barang Rusak' : 'Barang Hilang';
                    sns.forEach(s => {
                        html += `
                            <div class="sn-item-card p-2.5 border border-slate-200 rounded-lg bg-white space-y-2 shadow-2xs">
                                <div class="flex items-center justify-between">
                                    <label class="sn-item-label flex items-center gap-2 cursor-pointer select-none">
                                        <input type="checkbox" name="selected_sn_ids[]" value="${s.id}" class="rounded border-slate-300 text-zc focus:ring-zc sn-checkbox cursor-pointer" onchange="toggleSnItemReason(this, ${s.id})">
                                        <span class="font-mono text-xs font-bold text-slate-900">${s.serial_number}</span>
                                    </label>
                                    <span class="text-[10px] text-slate-400 font-medium">Status: ${s.status}</span>
                                </div>
                                <div id="sn_reason_box_${s.id}" class="hidden pt-2 border-t border-slate-100 space-y-1.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-[11px] text-slate-700 font-semibold">Kategori Status SN:</span>
                                        <select name="sn_reason[${s.id}]" class="border border-slate-300 rounded-lg px-2.5 py-1 text-xs font-semibold bg-white focus:ring-1 focus:ring-zc text-slate-800" onchange="toggleSnCustomInput(this, ${s.id})">
                                            <option value="Barang Rusak" ${defaultSnReason === 'Barang Rusak' ? 'selected' : ''}>Barang Rusak (Cacat Fisik / Fungsi)</option>
                                            <option value="Barang Hilang" ${defaultSnReason === 'Barang Hilang' ? 'selected' : ''}>Barang Hilang (Tidak Ditemukan di Rak)</option>
                                            <option value="Koreksi Salah Catat / Ditemukan">Koreksi Salah Catat Administrasi</option>
                                            <option value="Lainnya">Alasan Lainnya (Kustom)...</option>
                                        </select>
                                    </div>"""

assert old_alkes_fetch in content, "old_alkes_fetch not found"
content = content.replace(old_alkes_fetch, new_alkes_fetch)

# 3. Add helper guide banner inside container_alkes_sn
old_alkes_box = """                        <!-- Form Khusus Alkes (Pemilihan SN Spesifik) -->
                        <div id="container_alkes_sn" class="hidden space-y-3 p-3.5 bg-emerald-50/70 border border-emerald-200 rounded-xl">
                            <div id="alkes_minus_sn_box" class="space-y-2">"""

new_alkes_box = """                        <!-- Form Khusus Alkes (Pemilihan SN Spesifik) -->
                        <div id="container_alkes_sn" class="hidden space-y-3 p-3.5 bg-emerald-50/70 border border-emerald-200 rounded-xl">
                            <div class="p-2.5 bg-white border border-emerald-200 rounded-lg text-xs text-emerald-950 leading-relaxed">
                                <strong>Pelacakan Unit Fisik (Serial Number Tracking):</strong>
                                Centang nomor seri (SN) spesifik dari unit fisik yang terdampak, lalu pilih apakah unit tersebut berstatus <strong>Barang Rusak</strong> (cacat fisik/fungsi) atau <strong>Barang Hilang</strong> (tidak ditemukan di rak).
                            </div>
                            <div id="alkes_minus_sn_box" class="space-y-2">"""

assert old_alkes_box in content, "old_alkes_box not found"
content = content.replace(old_alkes_box, new_alkes_box)

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated inventori/stok_opname.php with Alkes Rusak scenario and enhanced SN selection!")

