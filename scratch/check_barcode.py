with open('inventori/stok_opname.php', 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()

import re
matches = re.findall(r'.{0,50}(?:scan|barcode).{0,50}', content, re.I)
print(f"Total scan/barcode occurrences: {len(matches)}")
for m in matches[:10]:
    print('-', m.strip())

