with open('lain/Gambar_3.18_Logical_Database_Model.svg', 'r', encoding='utf-8') as f:
    c = f.read()

import re
matches = re.findall(r'(<!-- Table:\s*(?:detail_opname_batch|sesi_opname)[\s\S]*?<\/g>)', c)
for m in matches:
    print(m)

