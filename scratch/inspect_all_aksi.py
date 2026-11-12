with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

import re
matches = re.findall(r"\$aksi\s*===?\s*['\"]([^'\"]+)['\"]", code)
print('Aksi values:', set(matches))
for m in sorted(set(matches)):
    print(f"- {m}")

