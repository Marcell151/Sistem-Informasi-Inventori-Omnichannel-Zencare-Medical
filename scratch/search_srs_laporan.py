# -*- coding: utf-8 -*-
with open('laporan_ta/DOKUMENTASI_SRS_LENGKAP_ZENCARE_MEDICAL.md', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = [m.start() for m in re.finditer(r'Laporan', text)]
for idx in matches[:10]:
    print(text[idx-20:idx+80])
    print('-'*40)

