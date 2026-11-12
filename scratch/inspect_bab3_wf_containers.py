with open("laporan_ta/BAB_III_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

import re

for i in range(1, 5):
    pattern = rf'<!-- Diagram Gambar 3\.{i} -->.*?</p>\s*</div>'
    m = re.search(pattern, text, re.DOTALL)
    if m:
        print(f"Found container for Gambar 3.{i}: length {len(m.group(0))}")
        print(m.group(0)[:150] + " ... " + m.group(0)[-100:])
        print("="*50)
    else:
        print(f"Gambar 3.{i} NOT FOUND!")

