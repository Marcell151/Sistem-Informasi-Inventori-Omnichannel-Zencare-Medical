with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re

figures = re.findall(r'<b>(Gambar\s+3\.\d+)</b>\s*([^<]+)', text)
print(f"Total figures found in BAB III: {len(figures)}")
for fig, caption in figures:
    print(f"  {fig}: {caption.strip()[:70]}")

