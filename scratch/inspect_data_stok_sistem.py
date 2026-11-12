with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

import re
matches = [m.start() for m in re.finditer(r'data-stok-sistem', code)]
for m in matches:
    print(code[m-50:m+200])
    print('---')

