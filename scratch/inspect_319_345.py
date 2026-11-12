with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = re.findall(r'(Gambar 3\.(?:19|[2-4][0-9])[^<]*)', text)
for m in matches:
    print(m.strip())

