with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

import re
matches = [m.start() for m in re.finditer(r'tutup_penghitungan', code)]
for pos in matches[1:]:
    print(code[pos:pos+800])
    print('===')

