with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re

pos = text.find('3.6 Wireframe')
pos_end = text.find('BAB IV', pos)
if pos_end == -1: pos_end = len(text)
block = text[pos:pos_end]

lines = block.splitlines()
for idx, l in enumerate(lines):
    if 'audit' in l.lower():
        print(f"L{idx+1}: {l.strip()[:120]}")

