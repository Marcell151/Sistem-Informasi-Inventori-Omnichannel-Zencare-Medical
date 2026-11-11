with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
m = re.search(r'id=[\'"]modal_mutasi[\'"].*?</form>', text, re.DOTALL)
if m:
    print(m.group(0)[:3000])

