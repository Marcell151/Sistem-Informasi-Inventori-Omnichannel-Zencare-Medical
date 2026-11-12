with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = [m.start() for m in re.finditer(r'3\.4\s+Logical Database', text, re.IGNORECASE)]
print('Matches:', matches)
for pos in matches:
    print(text[pos:pos+1500])

