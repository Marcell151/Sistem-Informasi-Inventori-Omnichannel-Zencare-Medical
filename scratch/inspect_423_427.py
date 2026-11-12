with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re

for fig in ['Gambar 4.23', 'Gambar 4.24', 'Gambar 4.25', 'Gambar 4.26', 'Gambar 4.27', 'Gambar 4.28']:
    pos = text.find(fig)
    start = max(0, pos - 600)
    end = min(len(text), pos + 200)
    chunk = text[start:end]
    srcs = re.findall(r'src=\"([^\"]+)\"', chunk)
    print(f"=== {fig} ===")
    print(f"  srcs in region: {srcs}")
