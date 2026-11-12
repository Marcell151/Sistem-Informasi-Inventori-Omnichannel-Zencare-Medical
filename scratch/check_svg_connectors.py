with open('lain/Gambar_3.18_Logical_Database_Model.svg', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re

# Find connections section
m_conn = re.search(r'<g id="connections".*?</g>\s*</svg>', text, re.DOTALL)
if m_conn:
    conn_block = m_conn.group(0)
    for line in conn_block.splitlines():
        if any(w in line for w in ['opname', 'sesi', '1270', '1530']):
            print(line[:140])

