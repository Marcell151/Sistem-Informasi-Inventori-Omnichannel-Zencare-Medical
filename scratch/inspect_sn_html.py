with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
m = re.search(r'let html = \'\';\s*sns\.forEach\(s => \{.*?\}\);', text, re.DOTALL)
if m:
    print(m.group(0))

