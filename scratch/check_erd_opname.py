with open('scratch/generate_orthogonal_erd.py', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re

# Find sesi_opname and detail_opname_batch definitions
for t in ['sesi_opname', 'detail_opname_batch', 'detail_opname_sn', 'riwayat_hitung_opname']:
    m = re.search(rf"'{t}':\s*\{{.*?\}}", text, re.DOTALL)
    if m:
        print(f"=== {t} ===")
        print(m.group(0))

# Find connections
m_conn = re.search(r'relationships\s*=\s*\[(.*?)\]', text, re.DOTALL)
if m_conn:
    print("\n=== relationships ===")
    lines = m_conn.group(1).splitlines()
    for l in lines:
        if any(w in l for w in ['opname', 'sesi']):
            print(l)

