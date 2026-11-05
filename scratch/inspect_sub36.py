with open(r'laporan_ta\BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = re.findall(r'(Gambar 3\.\d+[^<]+)', text)
for m in matches:
    print(m)

