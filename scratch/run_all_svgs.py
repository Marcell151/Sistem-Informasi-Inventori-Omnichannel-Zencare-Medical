# -*- coding: utf-8 -*-
"""
Master runner to save all 8 SVG files:
Gambar_3.10_Activity_Autentikasi.svg
Gambar_3.11_Activity_Pesanan_Daring.svg
Gambar_3.12_Activity_Pesanan_Kasir_POS.svg
Gambar_3.13_Activity_Kelola_Pesanan_Daring.svg
Gambar_3.14_Activity_Penerimaan_Barang.svg
Gambar_3.15_Activity_Mutasi_Stok.svg
Gambar_3.16_Activity_Laporan_Manajerial.svg
Gambar_3.17_Activity_Stok_Opname.svg
"""

import os
from build_all_svgs import get_svg_310, get_svg_311
from build_all_svgs_part2 import get_svg_312, get_svg_313
from build_all_svgs_part3 import get_svg_314, get_svg_315, get_svg_316, get_svg_317

target_dir = r'c:\xampp\htdocs\inventory_zencare\lain'
os.makedirs(target_dir, exist_ok=True)

files = [
    ('Gambar_3.10_Activity_Autentikasi.svg', get_svg_310()),
    ('Gambar_3.11_Activity_Pesanan_Daring.svg', get_svg_311()),
    ('Gambar_3.12_Activity_Pesanan_Kasir_POS.svg', get_svg_312()),
    ('Gambar_3.13_Activity_Kelola_Pesanan_Daring.svg', get_svg_313()),
    ('Gambar_3.14_Activity_Penerimaan_Barang.svg', get_svg_314()),
    ('Gambar_3.15_Activity_Mutasi_Stok.svg', get_svg_315()),
    ('Gambar_3.16_Activity_Laporan_Manajerial.svg', get_svg_316()),
    ('Gambar_3.17_Activity_Stok_Opname.svg', get_svg_317()),
]

for filename, content in files:
    path = os.path.join(target_dir, filename)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f'Saved {filename}: {len(content)} bytes')

print('All 8 Activity Diagram SVGs saved successfully!')

