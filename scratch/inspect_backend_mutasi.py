with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
m = re.search(r'//\s*---\s*2B\.\s*EKSEKUSI MUTASI.*?//\s*---\s*2C', text, re.DOTALL)
if m:
    print(m.group(0)[:3000])
else:
    print("Not found")

