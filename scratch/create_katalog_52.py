import json
import os
import zipfile
import re

# Read build_katalog_50.py
with open(r'scratch\build_katalog_50.py', 'r', encoding='utf-8') as f:
    code = f.read()

# Add items 51 and 52 before the closing bracket of items
items_addition = """,
    # 8. Pengujian Sistem (UAT)
    {
        "id": "ui_51", "fig": "Gambar 4.51", "title": "Lembar Blangko Kosong Instrumen Pengujian Penerimaan Pengguna (UAT)",
        "category": "testing", "role_badge": "Semua Peran", "badge_color": "bg-indigo-50 text-indigo-700 border-indigo-200",
        "filename": "51_Blangko_Instrumen_UAT.png", "folder": "08_Pengujian_Sistem_UAT",
        "desc": "Format blangko kosong instrumen pengujian penerimaan pengguna (UAT) cetak resmi, memuat identitas pengujian, matriks 36 skenario uji lapangan, kuesioner skala Likert 1-5, dan lembar pengesahan peran penguji."
    },
    {
        "id": "ui_52", "fig": "Gambar 4.52", "title": "Lembar Hasil Evaluasi Penerimaan Pengguna (UAT) Terisi Resmi",
        "category": "testing", "role_badge": "Semua Peran", "badge_color": "bg-indigo-50 text-indigo-700 border-indigo-200",
        "filename": "52_Hasil_Evaluasi_UAT_Terisi.png", "folder": "08_Pengujian_Sistem_UAT",
        "desc": "Lembar hasil evaluasi penerimaan pengguna (UAT) terisi resmi oleh perwakilan peran (Pemilik Toko, Admin Toko, Kasir Toko, dan Pelanggan), memuat status penerimaan 36 skenario, rekapitulasi skor 93.75% (Sangat Layak), dan lembar pengesahan."
    }
]"""

code = code.replace('\n    }\n]', '\n    }' + items_addition)

# Add filter button for testing
btn_add = '<button onclick="filterCategory(\'testing\', this)" class="filter-btn px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition cursor-pointer">Pengujian UAT</button>\n                    <button onclick="filterCategory(\'all\''

code = code.replace('<button onclick="filterCategory(\'all\'', btn_add)

# Update zip text/count from 50 to 52
code = code.replace('50 Antarmuka', '52 Antarmuka & Dokumen')
code = code.replace('50 Berkas PNG', '52 Berkas PNG')
code = code.replace('50 berkas citra', '52 berkas citra')

with open(r'scratch\build_katalog_52.py', 'w', encoding='utf-8') as f:
    f.write(code)

print("Saved scratch/build_katalog_52.py")

