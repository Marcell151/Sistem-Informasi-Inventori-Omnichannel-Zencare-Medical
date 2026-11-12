with open('scratch/generate_orthogonal_erd.py', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re
m_conn = re.search(r'relationships\s*=\s*\[(.*?)\]\s*\n\s*def', text, re.DOTALL)
if m_conn:
    print(m_conn.group(0))

