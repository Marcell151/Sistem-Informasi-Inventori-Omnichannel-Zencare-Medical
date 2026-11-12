with open('scratch/generate_orthogonal_erd.py', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re

# Extract tables_data block
m = re.search(r'tables_data\s*=\s*\{(.*?)\}\n\ncol_x', text, re.DOTALL)
if m:
    print("Found tables_data")
    lines = m.group(1).splitlines()
    cur_table = None
    for l in lines:
        m_t = re.match(r"\s*'([a-zA-Z0-9_]+)':\s*\{", l)
        if m_t:
            cur_table = m_t.group(1)
            print(f"Table: {cur_table}")

