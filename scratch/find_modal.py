with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
m1 = re.search(r'id=["\']modal_mutasi["\']', text)
if m1:
    line_no = text[:m1.start()].count('\n') + 1
    print('modal_mutasi line:', line_no)

m2 = re.search(r'function openModalMutasi', text)
if m2:
    line_no2 = text[:m2.start()].count('\n') + 1
    print('openModalMutasi line:', line_no2)

