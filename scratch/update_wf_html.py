with open('lain/wireframe_zencare.html', 'r', encoding='utf-8') as f:
    text = f.read()

# Replace stok_opname entry
old_opname_pattern = r'\{\s*"id":\s*"stok_opname".*?\}'
new_opname_entry = """{
    "id": "stok_opname",
    "category": "B. Antarmuka Admin & Kasir",
    "fig": "Gambar 3.29",
    "title": "Wireframe Antarmuka Stok Opname Persediaan",
    "sub": "Pencacahan fisik 3 kondisi, simpan bertahap, dan verifikasi selisih",
    "role": "Superadmin, Admin Toko, Kasir",
    "png": "File PPT/Wireframe/Stok Opname.png",
    "tabel": "sesi_opname, detail_opname_batch, detail_opname_sn, riwayat_hitung_opname",
    "desc": "Panel pelaksanaan pemeriksaan fisik persediaan berkala dengan segregasi peran operasional: kasir mencatat kuantitas fisik rak secara mandiri menggunakan metode pencacahan buta (blind count) dengan 3 kondisi fisik (Baik, Rusak, Expired) serta fitur simpan parsial bertahap; admin toko memverifikasi selisih dan mengeksekusi mutasi rekonsiliasi; sedangkan superadmin mengelola inisiasi pembekuan operasional (freeze lock) pada kasir POS dan penjualan daring, pembukaan kembali sesi, serta pencetakan Berita Acara Opname resmi."
  }"""

import re
text = re.sub(old_opname_pattern, new_opname_entry, text, flags=re.DOTALL)

# Replace other audit terms in wireframe_zencare.html
text = text.replace('"Wireframe Antarmuka Kartu Stok Audit"', '"Wireframe Antarmuka Kartu Stok Persediaan"')
text = text.replace('"Wireframe Antarmuka Stok Opname & Audit Fisik Persediaan"', '"Wireframe Antarmuka Stok Opname Persediaan"')
text = text.replace('"Wireframe Antarmuka Laporan Audit Anomali FEFO"', '"Wireframe Antarmuka Laporan Anomali FEFO"')
text = text.replace('"Audit masa simpan batch FEFO dan pelacakan serial alkes"', '"Pemantauan masa simpan batch FEFO dan pelacakan serial alkes"')
text = text.replace('"Audit inbound pasokan obat & alat kesehatan dari distributor"', '"Pemeriksaan inbound pasokan obat & alat kesehatan dari distributor"')
text = text.replace('"Audit pemecahan dus (UOM) & penyesuaian non-transaksional"', '"Pengawasan pemecahan dus (UOM) & penyesuaian non-transaksional"')
text = text.replace('"Pusat audit manajerial', '"Pusat pengawasan manajerial')
text = text.replace('"Audit kepatuhan kasir & deviasi pengambilan batch obat"', '"Evaluasi kepatuhan kasir & deviasi pengambilan batch obat"')
text = text.replace('"Laporan audit kepatuhan kasir', '"Laporan evaluasi kepatuhan kasir')
text = text.replace('audit logistik kesehatan', 'pengendalian logistik kesehatan')
text = text.replace('audit persediaan', 'persediaan')
text = text.replace('audit manajerial', 'pengawasan manajerial')
text = re.sub(r'\baudit\b', 'pemeriksaan', text, flags=re.IGNORECASE)

with open('lain/wireframe_zencare.html', 'w', encoding='utf-8') as f:
    f.write(text)

print("Updated lain/wireframe_zencare.html successfully!")

