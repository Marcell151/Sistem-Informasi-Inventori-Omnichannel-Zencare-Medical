with open('lain/wireframe_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

import re
for i, l in enumerate(lines):
    if any(k in l.lower() for k in ['stok opname', 'audit', 'terminal', '3.29']):
        print(f"L{i+1}: {l.strip()[:120]}")

