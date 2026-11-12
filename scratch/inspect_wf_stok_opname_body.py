with open('scratch/wf_stok_opname.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re

# Find the main body or structure of wf_stok_opname.html
pos = text.find('<body>')
print(text[pos:pos+4000])

