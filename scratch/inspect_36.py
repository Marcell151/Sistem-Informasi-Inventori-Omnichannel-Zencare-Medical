with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
pos = text.find('3.6 Wireframe')
if pos == -1:
    pos = text.find('3.6')

print(text[pos:pos+1500])

