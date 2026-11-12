with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches_data = [m.start() for m in re.finditer(r'data-stok-sistem', text)]
print("data-stok-sistem occurrences:", len(matches_data))
for m in matches_data:
    print(text[m-30:m+60])

matches_exp = [m.start() for m in re.finditer(r'bulk_fisik_expired', text)]
print("bulk_fisik_expired occurrences:", len(matches_exp))
for m in matches_exp:
    print(text[m-20:m+80])

