import re

with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace JS functions from quickFillReason to end of openModalMutasi
js_search_start = "function quickFillReason(type) {"
js_search_end = "document.getElementById('modal_mutasi').classList.remove('hidden');"

idx_start = content.find(js_search_start)
idx_end = content.find(js_search_end)

if idx_start == -1 or idx_end == -1:
    print("Error: Could not locate JS target in stok_opname.php")
    exit(1)

new_js = """let currentSelectedKategori = 'rusak';

function selectKategoriCard(kat) {
    currentSelectedKategori = kat;
    
    // Update Radio Check
    let radios = document.querySelectorAll('input[name="kategori_beban_radio"]');
    radios.forEach(r => {
        r.checked = (r.value === kat);
    });

    // Update Card Styling
    let allCards = ['rusak', 'hilang', 'expired', 'salah_catat', 'lainnya'];
    allCards.forEach(c => {
        let cardEl = document.getElementById('card_kat_' + c);
        if (cardEl) {
            if (c === kat) {
                cardEl.classList.add('border-slate-900', 'bg-slate-50', 'ring-1', 'ring-slate-900');
                cardEl.classList.remove('border-slate-200', 'bg-white');
            } else {
                cardEl.classList.remove('border-slate-900', 'bg-slate-50', 'ring-1', 'ring-slate-900');
                cardEl.classList.add('border-slate-200', 'bg-white');
            }
        }
    });

    // Handle Teks Alasan Lainnya
    let boxTeks = document.getElementById('box_teks_alasan_lainnya');
    let inputTeks = document.getElementById('teks_alasan_lainnya');
    if (boxTeks) {
        if (kat === 'lainnya') {
            boxTeks.classList.remove('hidden');
            if (inputTeks) inputTeks.focus();
        } else {
            boxTeks.classList.add('hidden');
        }
    }

    let isSplit = document.getElementById('toggle_split_kategori')?.checked || false;
    let satuan = currentOpnameItem ? (currentOpnameItem.nama_satuan || 'Unit') : 'Unit';

    if (!isSplit) {
        // Alokasi 100% langsung ke kategori terpilih
        allCards.forEach(c => {
            let el = document.getElementById('alokasi_' + c);
            if (el) el.value = (c === kat) ? currentAbsSelisih : 0;
        });

        let names = {
            'rusak': 'Mutasi Barang Rusak',
            'hilang': 'Mutasi Barang Hilang',
            'expired': 'Mutasi Barang Kedaluwarsa',
            'salah_catat': 'Koreksi Salah Catat Administrasi',
            'lainnya': 'Alasan Lainnya (Kustom)'
        };

        let teksPenuh = document.getElementById('teks_alokasi_penuh');
        let badgeQty = document.getElementById('badge_alokasi_penuh_qty');
        if (teksPenuh) teksPenuh.innerText = '100% dialokasikan ke ' + (names[kat] || kat);
        if (badgeQty) badgeQty.innerText = currentAbsSelisih + ' ' + satuan;

        let btnSubmit = document.getElementById('btn_submit_mutasi');
        if (btnSubmit) btnSubmit.disabled = false;
    }

    updateAllocationStatus();
}

function toggleSplitKategoriMode(isSplit) {
    let boxSplit = document.getElementById('box_split_kuantitas');
    let bannerPenuh = document.getElementById('banner_alokasi_penuh');

    if (isSplit) {
        if (boxSplit) boxSplit.classList.remove('hidden');
        if (bannerPenuh) bannerPenuh.classList.add('hidden');
    } else {
        if (boxSplit) boxSplit.classList.add('hidden');
        if (bannerPenuh) bannerPenuh.classList.remove('hidden');
        // Reset kembali ke 100% pada kartu terpilih
        selectKategoriCard(currentSelectedKategori);
    }
    updateAllocationStatus();
}

function quickFillReason(type) {
    selectKategoriCard(type);
}

function applyKategoriBeban(kat) {
    selectKategoriCard(kat);
}

function toggleSurplusCustomText(val) {
    let box = document.getElementById('box_surplus_teks_lainnya');
    if (box) {
        if (val === 'Lainnya') {
            box.classList.remove('hidden');
            document.getElementById('teks_surplus_lainnya')?.focus();
        } else {
            box.classList.add('hidden');
        }
    }
}

function toggleSnCustomInput(selectEl, snId) {
    let customInput = document.getElementById('sn_custom_input_' + snId);
    if (customInput) {
        if (selectEl.value === 'Lainnya') {
            customInput.classList.remove('hidden');
            customInput.focus();
        } else {
            customInput.classList.add('hidden');
        }
    }
}

function openModalMutasi(item) {
    currentOpnameItem = item;
    let selisih = parseInt(item.selisih) || 0;
    let isMinus = (selisih < 0);
    let absSel = Math.abs(selisih);
    currentAbsSelisih = absSel;

    document.getElementById('mutasi_id_detail').value = item.id;
    document.getElementById('mutasi_id_batch_target').value = item.id_batch || '';
    document.getElementById('mutasi_nama_produk').innerText = item.nama_produk + ' — ' + item.nama_variasi;
    
    let isObat = (item.jenis_komoditas === 'Obat');
    document.getElementById('mutasi_komoditas_badge').innerText = isObat ? 'Obat' : 'Alat Kesehatan';
    document.getElementById('mutasi_komoditas_badge').className = isObat ? 'font-bold text-blue-700' : 'font-bold text-emerald-700';

    let selBadge = document.getElementById('mutasi_selisih_badge');
    selBadge.innerText = (selisih > 0 ? '+' + selisih : selisih) + ' ' + (item.nama_satuan || 'Unit');
    selBadge.className = isMinus ? 'font-extrabold text-sm text-rose-600' : 'font-extrabold text-sm text-blue-600';

    let containerObat = document.getElementById('container_obat_batch');
    let containerAlkes = document.getElementById('container_alkes_sn');
    let containerDefisit = document.getElementById('container_alokasi_defisit');
    let containerSurplus = document.getElementById('container_alokasi_surplus');
    let cardExpired = document.getElementById('card_kat_expired');
    let rowExpired = document.getElementById('row_alokasi_expired');
    let btnSubmit = document.getElementById('btn_submit_mutasi');

    if (isObat) {
        containerObat.classList.remove('hidden');
        containerAlkes.classList.add('hidden');
        document.getElementById('mutasi_batch_display_no').innerText = item.no_batch || 'Batch Terdaftar';
        document.getElementById('mutasi_batch_display_exp').innerText = item.tgl_exp ? item.tgl_exp : '-';
        document.getElementById('mutasi_batch_satuan_label').innerText = item.nama_satuan || 'Satuan';
        if (cardExpired) cardExpired.classList.remove('hidden');
        if (rowExpired) rowExpired.classList.remove('hidden');
    } else {
        containerObat.classList.add('hidden');
        if (cardExpired) cardExpired.classList.add('hidden');
        if (rowExpired) rowExpired.classList.add('hidden');
    }

    if (isMinus) {
        if (isObat) {
            containerDefisit.classList.remove('hidden');
            containerAlkes.classList.add('hidden');
            containerSurplus.classList.add('hidden');

            // Reset Split toggle
            let toggleSplit = document.getElementById('toggle_split_kategori');
            if (toggleSplit) toggleSplit.checked = false;
            toggleSplitKategoriMode(false);

            // Evaluasi apakah tanggal kedaluwarsa sudah terlewati atau ada rusak tercatat
            let defaultKat = 'hilang';
            if (parseInt(item.fisik_rusak) > 0) {
                defaultKat = 'rusak';
            } else if (parseInt(item.fisik_expired) > 0) {
                defaultKat = 'expired';
            } else if (item.tgl_exp) {
                let expD = new Date(item.tgl_exp);
                let today = new Date();
                today.setHours(0,0,0,0);
                if (expD <= today) defaultKat = 'expired';
            }

            selectKategoriCard(defaultKat);

            ['rusak', 'hilang', 'expired', 'salah_catat', 'lainnya'].forEach(f => {
                let el = document.getElementById('alokasi_' + f);
                if (el) {
                    el.max = absSel;
                    el.oninput = updateAllocationStatus;
                }
            });

            updateAllocationStatus();
        } else {
            containerDefisit.classList.add('hidden');
            containerAlkes.classList.remove('hidden');
            containerSurplus.classList.add('hidden');
            let minusBox = document.getElementById('alkes_minus_sn_box');
            let plusBox = document.getElementById('alkes_plus_sn_box');
            minusBox.classList.remove('hidden');
            plusBox.classList.add('hidden');
            document.getElementById('count_sn_needed').innerText = absSel;
            if (btnSubmit) btnSubmit.disabled = true;

            let snListContainer = document.getElementById('alkes_sn_checkbox_list');
            snListContainer.innerHTML = '<div class="text-xs text-slate-400 py-2 text-center">Memuat Serial Number aktif...</div>';

            fetch('../api/get_batch_sn.php?action=get_available_sns&id_variasi=' + item.id_variasi)
                .then(r => r.json())
                .then(data => {
                    let sns = data.sns || data.items || [];
                    if (!sns.length) {
                        snListContainer.innerHTML = '<div class="text-xs text-rose-600 p-2">Tidak ada SN berstatus Tersedia di database.</div>';
                        return;
                    }
                    let html = '';
                    let defaultSnReason = (parseInt(item.fisik_rusak) > 0) ? 'Barang Rusak' : 'Barang Hilang';
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
                                    </div>
                                    <input type="text" name="sn_custom_reason[${s.id}]" id="sn_custom_input_${s.id}" placeholder="Tuliskan penyebab spesifik untuk SN ini..." class="hidden w-full border border-slate-300 rounded px-2 py-1 text-xs bg-slate-50 focus:bg-white text-slate-700">
                                </div>
                            </div>
                        `;
                    });
                    snListContainer.innerHTML = html;
                    updateAlkesSnCheckboxes();
                })
                .catch(() => {
                    snListContainer.innerHTML = '<div class="text-xs text-rose-600 p-2">Gagal memuat SN.</div>';
                });
        }
    } else {
        containerDefisit.classList.add('hidden');
        containerSurplus.classList.remove('hidden');
        let selSurplus = document.getElementById('alasan_mutasi_surplus');
        if (selSurplus) selSurplus.value = 'Koreksi Salah Catat / Ditemukan';
        toggleSurplusCustomText('Koreksi Salah Catat / Ditemukan');
        if (btnSubmit) btnSubmit.disabled = false;

        if (!isObat) {
            containerAlkes.classList.remove('hidden');
            let minusBox = document.getElementById('alkes_minus_sn_box');
            let plusBox = document.getElementById('alkes_plus_sn_box');
            minusBox.classList.add('hidden');
            plusBox.classList.remove('hidden');
        }
    }

    document.getElementById('modal_mutasi').classList.remove('hidden');"""

content = content[:idx_start] + new_js + content[idx_end + len(js_search_end):]
print("✓ JS functions replaced successfully.")

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(content)

