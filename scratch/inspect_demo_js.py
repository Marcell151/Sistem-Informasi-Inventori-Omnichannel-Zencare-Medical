with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
m = re.search(r'function isiOtomatisAngkaDemo\(\)\s*\{.*?^\}', text, re.DOTALL | re.MULTILINE)
if m:
    print(m.group(0))

