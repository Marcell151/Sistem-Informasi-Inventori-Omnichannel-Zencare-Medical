with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

import re
matches = re.findall(r"\$_POST\[['\"][a-zA-Z0-9_]+['\"]\]", code)
print('POST keys:', set(matches))

matches_get = re.findall(r"\$_GET\[['\"][a-zA-Z0-9_]+['\"]\]", code)
print('GET keys:', set(matches_get))

