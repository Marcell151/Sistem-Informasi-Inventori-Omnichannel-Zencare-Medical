with open('scratch/generate_orthogonal_erd.py', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re
lines = text.splitlines()
for i, l in enumerate(lines):
    if 'sesi_opname' in l or 'detail_opname' in l:
        print(f"L{i+1}: {l}")

