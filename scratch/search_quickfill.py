with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = [m.start() for m in re.finditer(r'quickfill|isi_cepat', text, re.IGNORECASE)]
for pos in matches:
    print(text[pos-50:pos+100])
    print('---')

