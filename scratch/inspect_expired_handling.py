with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

import re
matches = [m.start() for m in re.finditer(r'input_exp', code)]
for m in matches[:5]:
    print(code[m-50:m+250])
    print('---')

matches_exp = [m.start() for m in re.finditer(r'isObatExp', code)]
for m in matches_exp[:5]:
    print(code[m-50:m+250])
    print('===')

