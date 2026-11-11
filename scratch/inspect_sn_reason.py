with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
m = re.search(r'function toggleSnItemReason.*?\n\}', text, re.DOTALL)
if m:
    print(m.group(0))
else:
    print("toggleSnItemReason not found")

m2 = re.search(r'<div id=\"sn_reason_box_.*?</div>', text, re.DOTALL)
if m2:
    print(m2.group(0)[:500])

