with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

import re
matches = re.findall(r"(?:include|require)[^;]+", code)
for m in matches[:10]:
    print(m)

