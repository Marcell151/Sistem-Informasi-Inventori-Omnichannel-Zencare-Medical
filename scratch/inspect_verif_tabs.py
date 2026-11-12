with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

import re
pos_verif = code.find('TAHAP 2: VERIFIKASI')
print(code[pos_verif:pos_verif+2000])
