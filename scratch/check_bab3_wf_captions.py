with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
wfs = re.findall(r'<b>(Gambar 3\.\d+)</b>\s*Wireframe Antarmuka ([^<]+)', text)
print(f'Total wireframe captions found in BAB 3: {len(wfs)}')
for num, title in wfs:
    print(f'{num}: {title.strip()}')

