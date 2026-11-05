# -*- coding: utf-8 -*-
"""
Master script to build and verify all 8 final SVGs
"""

import os
import re
import xml.etree.ElementTree as ET

from final_builder_part1 import get_diagram_310, get_diagram_311
from final_builder_part2 import get_diagram_312, get_diagram_313
from final_builder_part3 import get_diagram_314, get_diagram_315, get_diagram_316, get_diagram_317

target_dir = r'c:\xampp\htdocs\inventory_zencare\lain'
os.makedirs(target_dir, exist_ok=True)

svgs = [
    ('Gambar_3.10_Activity_Autentikasi.svg', get_diagram_310()),
    ('Gambar_3.11_Activity_Pesanan_Daring.svg', get_diagram_311()),
    ('Gambar_3.12_Activity_Pesanan_Kasir_POS.svg', get_diagram_312()),
    ('Gambar_3.13_Activity_Kelola_Pesanan_Daring.svg', get_diagram_313()),
    ('Gambar_3.14_Activity_Penerimaan_Barang.svg', get_diagram_314()),
    ('Gambar_3.15_Activity_Mutasi_Stok.svg', get_diagram_315()),
    ('Gambar_3.16_Activity_Laporan_Manajerial.svg', get_diagram_316()),
    ('Gambar_3.17_Activity_Stok_Opname.svg', get_diagram_317()),
]

def check_orthogonal(svg_text, filename):
    # Find all path 'd' attributes
    paths = re.findall(r'<path[^>]*d="([^"]+)"', svg_text)
    slanted = []
    for p in paths:
        # Ignore defs markers
        if 'M 0 1 L 10 5 L 0 9 z' in p:
            continue
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
                slanted.append((coords[j], coords[j+1]))
    
    # Check <line> elements too
    lines = re.findall(r'<line[^>]*x1="([^"]+)"[^>]*y1="([^"]+)"[^>]*x2="([^"]+)"[^>]*y2="([^"]+)"', svg_text)
    for x1_s, y1_s, x2_s, y2_s in lines:
        x1, y1, x2, y2 = float(x1_s), float(y1_s), float(x2_s), float(y2_s)
        if x1 != x2 and y1 != y2:
            slanted.append(((x1, y1), (x2, y2)))
            
    return slanted

print("=== BUILDING & VERIFYING ALL 8 ACTIVITY DIAGRAMS ===")
all_clean = True
for fname, content in svgs:
    fpath = os.path.join(target_dir, fname)
    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(content)
        
    # 1. XML check
    try:
        ET.parse(fpath)
        xml_ok = True
    except Exception as e:
        xml_ok = False
        print(f"ERROR: {fname} XML Invalid: {e}")
        all_clean = False
        
    # 2. 'farmasi' check
    farmasi_count = len(re.findall(r'farmasi', content, re.IGNORECASE))
    if farmasi_count > 0:
        print(f"ERROR: {fname} contains {farmasi_count} occurrences of 'farmasi'!")
        all_clean = False
        
    # 3. Orthogonality check
    slanted = check_orthogonal(content, fname)
    if slanted:
        print(f"WARNING: {fname} has slanted lines: {slanted}")
        all_clean = False
        
    status = "OK [100% Valid XML, 100% Orthogonal, 0 Farmasi]" if (xml_ok and farmasi_count == 0 and len(slanted) == 0) else "FAILED"
    print(f"{fname:<45}: {len(content):>6} bytes | {status}")

if all_clean:
    print("\nSUCCESS: All 8 Activity Diagrams generated and verified with 100% perfection!")
else:
    print("\nSome issues found. Please inspect above logs.")

