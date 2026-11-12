with open('lain/Gambar_3.18_Logical_Database_Model.svg', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re
m = re.search(r'<svg[^>]+>', text)
if m:
    print(m.group(0))

