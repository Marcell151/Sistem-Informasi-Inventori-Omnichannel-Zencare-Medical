with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re

pos = text.find('3.6 Wireframe')
pos_end = text.find('</section>', pos) # or whatever ends subbab 3.6
if pos_end == -1: pos_end = text.find('BAB IV', pos)
if pos_end == -1: pos_end = len(text)
block = text[pos:pos_end]

figures = re.findall(r'Gambar 3\.(\d+)\s+(Wireframe[^\n<]+)', block)
print(f"Total wireframe figures in Subbab 3.6: {len(figures)}")
for num, title in figures:
    print(f"Gambar 3.{num}: {title.strip()}")

# Also check headings A, B, C...
headings = re.findall(r'<h4[^>]*>\s*([A-Z]\.[^\n<]+)', block)
print(f"\nTotal headings: {len(headings)}")
for h in headings:
    print(f"  {h.strip()}")

