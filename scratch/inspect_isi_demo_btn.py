with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = [m.start() for m in re.finditer(r'isiOtomatisAngkaDemo', text)]
for pos in matches:
    print(text[pos-100:pos+150])
    print('---')

