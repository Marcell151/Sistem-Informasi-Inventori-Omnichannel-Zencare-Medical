# -*- coding: utf-8 -*-
"""
Script to replace 'farmasi' in BAB_III_ZENCARE_MEDICAL.html
"""

file_path = r'laporan_ta/BAB_III_ZENCARE_MEDICAL.html'

with open(file_path, 'r', encoding='utf-8') as f:
    text = f.read()

replacements = [
    ('Beli Langsung ke Grosir Farmasi & Bawa Fisik ke Toko', 'Beli Langsung ke Distributor Pemasok & Bawa Fisik ke Toko'),
    ('Beli Langsung ke Grosir Farmasi &amp; Bawa Fisik ke Toko', 'Beli Langsung ke Distributor Pemasok &amp; Bawa Fisik ke Toko'),
    ('langsung secara tunai ke pedagang besar farmasi rekanan', 'langsung secara tunai ke distributor pemasok rekanan'),
    ('ke pedagang besar farmasi rekanan', 'ke distributor rekanan resmi'),
    ('ke pedagang besar\n                                farmasi rekanan', 'ke distributor rekanan\n                                resmi'),
    ('kepatuhan audit farmasi', 'kepatuhan audit persediaan medis'),
    ('audit regulasi farmasi', 'audit standar peredaran obat dan alat kesehatan'),
    ('audit\n                                regulasi farmasi', 'audit standar peredaran\n                                obat dan alat kesehatan'),
    ('kepatuhan terhadap regulasi tata kelola kefarmasian', 'kepatuhan terhadap tata kelola distribusi obat dan alat kesehatan'),
    ('kepatuhan regulasi farmasi', 'kepatuhan standar mutu dan keamanan komoditas medis')
]

for old, new in replacements:
    if old in text:
        text = text.replace(old, new)
        print(f"Replaced: {old[:30]}... -> {new[:30]}...")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(text)

# Verify
import re
remaining = re.findall(r'.{0,30}farmasi.{0,30}', text, re.IGNORECASE)
print(f"Remaining 'farmasi' occurrences: {len(remaining)}")
for r in remaining:
    print("  ->", r.strip())

