with open('scratch/generate_orthogonal_erd.py', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re
pos = text.find('detail_opname_sn')
print(text[pos:pos+1500])

