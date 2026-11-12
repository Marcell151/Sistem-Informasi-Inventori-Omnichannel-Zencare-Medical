with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = re.findall(r'<p class=\"text-xs font-bold font-serif text-slate-900 mt-2\">\s*(Gambar 3\.(?:19|[2-4][0-9])\s*Wireframe Antarmuka [^\n<]+)', text)
for i, m in enumerate(matches):
    print(f"{i+1}. {m.strip()}")

