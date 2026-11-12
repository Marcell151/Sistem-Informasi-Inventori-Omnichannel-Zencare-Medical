with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = re.findall(r'SELECT.*sesi_opname.*', text, re.I)
for m in matches[:10]:
    print(m)

