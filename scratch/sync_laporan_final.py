import re

# 1. Update lain/kamus_data_zencare.html JSON
with open('lain/kamus_data_zencare.html', 'r', encoding='utf-8') as f:
    kd = f.read()

old_kd1 = '"name": "kartu_stok", "domain": "Buku Besar Mutasi", "pk": "id_kartu_stok", "fk": "id_variasi, dibuat_oleh"'
new_kd1 = '"name": "kartu_stok", "domain": "Buku Besar Mutasi", "pk": "id_kartu_stok", "fk": "id_variasi, id_batch, dibuat_oleh"'
assert old_kd1 in kd, "old_kd1 not found"
kd = kd.replace(old_kd1, new_kd1, 1)

m = re.search(r'"name": "log_anomali_fefo"[^\}]+?"fk": "id_penjualan, id_user, id_variasi"', kd)
assert m, "log_anomali_fefo fk not found in JSON"
kd = kd.replace(m.group(0), m.group(0).replace('"fk": "id_penjualan, id_user, id_variasi"', '"fk": "id_penjualan, id_user, id_variasi, resolved_in_penjualan"'), 1)

old_kd3 = '["resolved_in_penjualan", "Integer", "11", "ID transaksi penjualan tempat anomali berhasil diselesaikan."]'
new_kd3 = '["resolved_in_penjualan", "Integer", "11", "Foreign Key ke penjualan.id_penjualan tempat anomali berhasil diselesaikan."]'
assert old_kd3 in kd, "old_kd3 not found"
kd = kd.replace(old_kd3, new_kd3, 1)

with open('lain/kamus_data_zencare.html', 'w', encoding='utf-8') as f:
    f.write(kd)
print("Updated lain/kamus_data_zencare.html")

# 2. Update merged report files (BAB_III_DAN_IV and LAPORAN_TA_FULL)
merged_files = [
    'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html',
    'laporan_ta/LAPORAN_TA_ZENCARE_MEDICAL_FULL.html'
]

for path in merged_files:
    with open(path, 'r', encoding='utf-8') as f:
        txt = f.read()

    # A. kartu_stok metadata FK (only the one right after id_kartu_stok)
    pat_ks_meta = re.compile(
        r'(<p><b><i>Primary Key</i>:</b> <i>id_kartu_stok</i></p>\s*<p><b><i>Foreign Key</i>:</b> )<i>id_variasi</i>, <i>dibuat_oleh</i>(</p>)'
    )
    txt, n1 = pat_ks_meta.subn(r'\1<i>id_variasi</i>, <i>id_batch</i>, <i>dibuat_oleh</i>\2', txt, count=1)
    assert n1 == 1, f"n1={n1} in {path}"

    # B. kartu_stok id_batch row badge (FK)
    pat_ks_row = re.compile(
        r'(<td[^>]*>3</td>\s*<td[^>]*>)<i>id_batch</i>(</td>\s*<td[^>]*>Integer</td>\s*<td[^>]*>11</td>\s*<td[^>]*><i>Foreign Key</i> merujuk ke <i>stok_batch</i>)'
    )
    txt, n2 = pat_ks_row.subn(r'\1<i>id_batch</i> <span class="text-blue-700 font-bold font-sans text-[10px]">(FK)</span>\2', txt, count=1)
    assert n2 == 1, f"n2={n2} in {path}"

    # C. log_anomali_fefo metadata FK
    pat_la_meta = re.compile(
        r'(<p><b><i>Primary Key</i>:</b> <i>id_log_anomali</i></p>\s*<p><b><i>Foreign Key</i>:</b> )<i>id_penjualan</i>, <i>id_user</i>, <i>id_variasi</i>(</p>)'
    )
    txt, n3 = pat_la_meta.subn(r'\1<i>id_penjualan</i>, <i>id_user</i>, <i>id_variasi</i>, <i>resolved_in_penjualan</i>\2', txt, count=1)
    assert n3 == 1, f"n3={n3} in {path}"

    # D. log_anomali_fefo resolved_in_penjualan row badge (FK)
    pat_la_row = re.compile(
        r'(<td[^>]*>11</td>\s*<td[^>]*>)<i>resolved_in_penjualan</i>(</td>\s*<td[^>]*>Integer</td>\s*<td[^>]*>11</td>\s*<td[^>]*>)ID transaksi penjualan tempat anomali berhasil diselesaikan\.(</td>)'
    )
    txt, n4 = pat_la_row.subn(r'\1<i>resolved_in_penjualan</i> <span class="text-blue-700 font-bold font-sans text-[10px]">(FK)</span>\2<i>Foreign Key</i> ke penjualan.<i>id_penjualan</i> tempat anomali berhasil diselesaikan.\3', txt, count=1)
    assert n4 == 1, f"n4={n4} in {path}"

    # E. Bab IV Opname text update (Grand Total)
    old_b4 = "Dokumentasi siklus utuh pemeriksaan fisik berkala persediaan persediaan (<i>stok opname</i>)"
    new_b4 = "Dokumentasi siklus utuh pemeriksaan fisik berkala persediaan (<i>stok opname</i>)"
    assert old_b4 in txt, f"old_b4 not found in {path}"
    txt = txt.replace(old_b4, new_b4, 1)

    old_t3 = "(Jumlah Rusak + Jumlah Expired + Jumlah Hilang) &times; Harga Beli (Satuan Terkecil)."
    new_t3 = "(Jumlah Rusak + Jumlah Expired + Jumlah Hilang) &times; Harga Beli (Satuan Terkecil), serta menampilkan akumulasi Total Keseluruhan (<i>Grand Total</i>) Estimasi Nilai Kerugian Persediaan pada baris paling bawah tabel verifikasi."
    assert old_t3 in txt, f"old_t3 not found in {path}"
    txt = txt.replace(old_t3, new_t3, 1)

    old_t4 = "Nilai Kerugian, dan Tindakan Mutasi. Bagian bawah lembar memuat"
    new_t4 = "Nilai Kerugian, dan Tindakan Mutasi, lengkap dengan baris ringkasan Total Keseluruhan (<i>Grand Total</i>) Estimasi Nilai Kerugian Persediaan di bagian bawah tabel. Bagian bawah lembar memuat"
    assert old_t4 in txt, f"old_t4 not found in {path}"
    txt = txt.replace(old_t4, new_t4, 1)

    with open(path, 'w', encoding='utf-8') as f:
        f.write(txt)
    print(f"Updated {path} (n1={n1}, n2={n2}, n3={n3}, n4={n4})")

