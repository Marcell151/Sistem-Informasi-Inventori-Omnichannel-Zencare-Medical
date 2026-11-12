with open("laporan_ta/BAB_III_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

import re

for i in range(1, 5):
    m = re.search(rf'<!-- Diagram Gambar 3\.{i} -->.*?</p>', text, re.DOTALL)
    if m:
        print(f"=== GAMBAR 3.{i} ===")
        print(m.group(0))
        print("="*50)

