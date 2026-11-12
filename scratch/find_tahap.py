with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = re.finditer(r'if\s*\([^)]*tahap[^)]*\)', text)
for m in matches:
    print(m.group(0), "at pos", m.start())

