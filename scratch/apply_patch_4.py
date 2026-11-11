with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update simpanSatuBaris to respect Kasir Blind Count
simpan_old = """    let valFisik = parseInt(inputEl.value, 10);
    let valSistem = parseInt(inputEl.dataset.sistem, 10);

    // Jika terdeteksi selisih fisik vs sistem, berikan konfirmasi langsung
    if (!isNaN(valSistem) && valFisik !== valSistem) {"""

simpan_new = """    let valFisik = parseInt(inputEl.value, 10);
    let valSistem = parseInt(inputEl.dataset.sistem, 10);
    let isKasir = <?= json_encode($isKasir) ?>;

    // Untuk Kasir: Strict Blind Count (TIDAK BOLEH memunculkan popup selisih atau angka sistem!)
    if (isKasir || isNaN(valSistem)) {
        document.getElementById('single_save_id').value = idDetail;
        document.getElementById('single_save_val').value = valFisik;
        document.getElementById('form_single_save').submit();
        return;
    }

    // Untuk Admin / Superadmin: Jika terdeteksi selisih fisik vs sistem, berikan konfirmasi langsung
    if (valFisik !== valSistem) {"""

assert simpan_old in content, "simpan_old not found!"
content = content.replace(simpan_old, simpan_new, 1)

# 2. Add applyKategoriBeban function and update openModalMutasi
modal_js_old = """            // Default alokasi: seluruh selisih diset ke Barang Rusak agar user bisa langsung klik simpan
            document.getElementById('alokasi_rusak').value = absSel;
            document.getElementById('alokasi_hilang').value = 0;
            document.getElementById('alokasi_expired').value = 0;
            document.getElementById('alokasi_salah_catat').value = 0;
            if (document.getElementById('alokasi_lainnya')) document.getElementById('alokasi_lainnya').value = 0;
            if (document.getElementById('teks_alasan_lainnya')) document.getElementById('teks_alasan_lainnya').value = '';

            ['rusak', 'hilang', 'expired', 'salah_catat', 'lainnya'].forEach(f => {
                let el = document.getElementById('alokasi_' + f);
                if (el) {
                    el.max = absSel;
                    el.oninput = updateAllocationStatus;
                }
            });

            updateAllocationStatus();"""

modal_js_new = """            // Evaluasi apakah tanggal kedaluwarsa sudah terlewati
            let isExpAlready = false;
            if (item.tgl_exp) {
                let expD = new Date(item.tgl_exp);
                let today = new Date();
                today.setHours(0,0,0,0);
                if (expD <= today) isExpAlready = true;
            }

            let selKat = document.getElementById('kategori_beban_utama');
            let defaultKat = isExpAlready ? 'expired' : 'rusak';
            if (selKat) {
                selKat.value = defaultKat;
            }

            // Terapkan alokasi kuantitas instan sesuai dropdown kategori
            applyKategoriBeban(defaultKat);

            ['rusak', 'hilang', 'expired', 'salah_catat', 'lainnya'].forEach(f => {
                let el = document.getElementById('alokasi_' + f);
                if (el) {
                    el.max = absSel;
                    el.oninput = updateAllocationStatus;
                }
            });

            updateAllocationStatus();"""

assert modal_js_old in content, "modal_js_old not found!"
content = content.replace(modal_js_old, modal_js_new, 1)

# 3. Add applyKategoriBeban helper function right after quickFillReason
quick_fill_old = """function quickFillReason(type) {
    let fields = ['rusak', 'hilang', 'expired', 'salah_catat', 'lainnya'];
    fields.forEach(f => {
        let el = document.getElementById('alokasi_' + f);
        if (el) {
            el.value = (f === type) ? currentAbsSelisih : 0;
        }
    });
    updateAllocationStatus();
}"""

quick_fill_new = """function quickFillReason(type) {
    let fields = ['rusak', 'hilang', 'expired', 'salah_catat', 'lainnya'];
    fields.forEach(f => {
        let el = document.getElementById('alokasi_' + f);
        if (el) {
            el.value = (f === type) ? currentAbsSelisih : 0;
        }
    });
    let selKat = document.getElementById('kategori_beban_utama');
    if (selKat) selKat.value = type;
    updateAllocationStatus();
}

// Fungsi Penerapan Kategori Beban Selisih (Dropdown Mutasi) Sesuai Arahan Dosen
function applyKategoriBeban(kat) {
    if (kat === 'custom') {
        return; // Pengguna mengisi kuantitas gabungan secara manual
    }
    let fields = ['rusak', 'hilang', 'expired', 'salah_catat', 'lainnya'];
    fields.forEach(f => {
        let el = document.getElementById('alokasi_' + f);
        if (el) {
            el.value = (f === kat) ? currentAbsSelisih : 0;
        }
    });
    updateAllocationStatus();
}"""

assert quick_fill_old in content, "quick_fill_old not found!"
content = content.replace(quick_fill_old, quick_fill_new, 1)

# 4. Add Barcode Scanner JS at end of script
end_script_old = """document.getElementById('form_mutasi_selisih')?.addEventListener('submit', function(e) {
    let minusBox = document.getElementById('alkes_minus_sn_box');
    if (!minusBox.classList.contains('hidden') && !document.getElementById('container_alkes_sn').classList.contains('hidden')) {
        let needed = parseInt(document.getElementById('count_sn_needed').innerText, 10);
        let checked = document.querySelectorAll('.sn-checkbox:checked').length;
        if (checked !== needed) {
            e.preventDefault();
            alert('Harap pilih tepat ' + needed + ' nomor Serial Number yang rusak/hilang! (Saat ini dipilih: ' + checked + ')');
        }
    }
});
</script>"""

end_script_new = """document.getElementById('form_mutasi_selisih')?.addEventListener('submit', function(e) {
    let minusBox = document.getElementById('alkes_minus_sn_box');
    if (!minusBox.classList.contains('hidden') && !document.getElementById('container_alkes_sn').classList.contains('hidden')) {
        let needed = parseInt(document.getElementById('count_sn_needed').innerText, 10);
        let checked = document.querySelectorAll('.sn-checkbox:checked').length;
        if (checked !== needed) {
            e.preventDefault();
            alert('Harap pilih tepat ' + needed + ' nomor Serial Number yang rusak/hilang! (Saat ini dipilih: ' + checked + ')');
        }
    }
});

// Pemindai Barcode / Fast Locator untuk Pencacahan Fisik Rak
function handleBarcodeScan() {
    let input = document.getElementById('barcode_scan_input');
    let q = (input ? input.value : '').trim().toLowerCase();
    if (!q) return;

    let rows = document.querySelectorAll('tr[data-barcode-search]');
    let matchedRow = null;

    rows.forEach(r => {
        let str = (r.getAttribute('data-barcode-search') || '').toLowerCase();
        if (str.includes(q) && !matchedRow) {
            matchedRow = r;
        }
    });

    if (matchedRow) {
        matchedRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        matchedRow.classList.add('bg-amber-100', 'ring-2', 'ring-amber-500');
        setTimeout(() => {
            matchedRow.classList.remove('bg-amber-100', 'ring-2', 'ring-amber-500');
        }, 3000);

        let inputFisik = matchedRow.querySelector('.input-hitung-fisik');
        if (inputFisik) {
            inputFisik.focus();
            inputFisik.select();
        }
        input.value = '';
    } else {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Barang Tidak Ditemukan',
                text: 'Barcode / SKU / Batch "' + q + '" tidak ditemukan dalam daftar opname ini.',
                icon: 'warning',
                timer: 2500,
                showConfirmButton: false
            });
        } else {
            alert('Barcode / SKU / Batch "' + q + '" tidak ditemukan!');
        }
    }
}

document.getElementById('barcode_scan_input')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        handleBarcodeScan();
    }
});
</script>"""

assert end_script_old in content, "end_script_old not found!"
content = content.replace(end_script_old, end_script_new, 1)

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Part 4 updated successfully.")

