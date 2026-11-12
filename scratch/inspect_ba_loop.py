with open('inventori/cetak_berita_acara_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

import re
matches = [m.start() for m in re.finditer(r'foreach\s*\(', code)]
for pos in matches:
    print(code[pos:pos+300])
    print('---')

