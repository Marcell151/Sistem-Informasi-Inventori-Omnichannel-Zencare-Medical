# -*- coding: utf-8 -*-
"""
Runner to generate all 8 SVGs and validate:
- XML syntax
- Orthogonality (strictly 0, 90, 180, 270 degree segments)
- Absence of 'farmasi'
"""

import os
import re
import xml.etree.ElementTree as ET

from refined_svgs_part1 import get_refined_310, get_refined_311
from refined_svgs_part2 import get_refined_312, get_refined_313
from refined_svgs_part3 import get_refined_314, get_refined_315, get_refined_316, get_refined_317

target_dir = r'c:\xampp\htdocs\inventory_zencare\lain'

svgs = [
    ('Gambar_3.10_Activity_Autentikasi.svg', get_refined_310()),
    ('Gambar_3.11_Activity_Pesanan_Daring.svg', get_refined_311()),
    ('Gambar_3.12_Activity_Pesanan_Kasir_POS.svg', get_refined_312()),
    ('Gambar_3.13_Activity_Kelola_Pesanan_Daring.svg', get_refined_313()),
    ('Gambar_3.14_Activity_Penerimaan_Barang.svg', get_refined_314()),
    ('Gambar_3.15_Activity_Mutasi_Stok.svg', get_refined_315()),
    ('Gambar_3.16_Activity_Laporan_Manajerial.svg', get_refined_316()),
    ('Gambar_3.17_Activity_Stok_Opname.svg', get_refined_317()),
]

def check_orthogonal(svg_text, filename):
    # Find all path 'd' attributes
    paths = re.findall(r'<path[^>]*d="([^"]+)"', svg_text)
    for p in paths:
        # Match tokens like M x y L x y ...
        tokens = p.strip().split()
        coords = []
        i = 0
        while i < len(tokens):
            cmd = tokens[i]
            if cmd in ['M', 'L']:
                x = float(tokens[i+1])
                y = float(tokens[i+2])
                coords.append((x, y))
                i += 3
            else:
                i += 1
        for j in range(len(coords) - 1):
            x1, y1 = coords[j]
            x2, y2 = coords[j+1]
            if x1 != x2 and y1 != y2:
                print(f"WARNING: Slanted segment in {filename}: ({x1},{y1}) -> ({x2},{y2})")

print("Processing SVGs...")
for fname, content in svgs:
    fpath = os.path.join(target_dir, fname)
    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(content)
    
    # XML Validation
    try:
        ET.parse(fpath)
        xml_status = "VALID XML"
    except Exception as e:
        xml_status = f"XML ERROR: {e}"
        
    # Farmasi check
    farmasi_count = len(re.findall(r'farmasi', content, re.IGNORECASE))
    
    print(f"{fname}: {len(content)} bytes | {xml_status} | 'farmasi' count: {farmasi_count}")
    check_orthogonal(content, fname)

print("All SVGs processed successfully!")

