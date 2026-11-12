with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

import re
matches = [m.start() for m in re.finditer(r"\$_POST\['aksi'\]", code)]
for pos in matches:
    print(code[pos-20:pos+150])
    print('---')

