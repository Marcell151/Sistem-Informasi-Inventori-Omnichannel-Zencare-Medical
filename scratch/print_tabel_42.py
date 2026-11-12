with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re

pos = text.find('Tabel 4.2')
print(text[pos:pos+1500])

