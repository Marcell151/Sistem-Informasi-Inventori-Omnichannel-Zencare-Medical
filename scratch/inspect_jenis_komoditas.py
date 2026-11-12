with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

import re
matches = [m.start() for m in re.finditer(r'jenis_komoditas', code)]
print(f'jenis_komoditas occurrences: {len(matches)}')
for m in matches[:5]:
    print(code[m-20:m+150])
    print('---')

