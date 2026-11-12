with open('lain/logical_database_zencare.html', 'r', encoding='utf-8') as f:
    c = f.read()

import re
matches = re.findall(r'(Table\s+detail_opname_batch[\s\S]*?\})', c)
for m in matches:
    print("=== MATCH ===")
    print(m)

matches_sesi = re.findall(r'(Table\s+sesi_opname[\s\S]*?\})', c)
for m in matches_sesi:
    print("=== SESI MATCH ===")
    print(m)

