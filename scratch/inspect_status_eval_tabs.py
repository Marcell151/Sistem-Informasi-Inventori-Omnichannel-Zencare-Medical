with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

import re
matches = [m.start() for m in re.finditer(r'status_eval', code)]
for pos in matches[:5]:
    print(code[pos-100:pos+250])
    print('---')
