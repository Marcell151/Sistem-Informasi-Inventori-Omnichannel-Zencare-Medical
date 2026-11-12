with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = [m.start() for m in re.finditer(r'reset_database_demo', text)]
for pos in matches:
    print(text[pos-100:pos+250])
    print('---')

