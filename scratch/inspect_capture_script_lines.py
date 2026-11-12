with open(r'scratch\capture_missing_and_adjusted_ui.py', 'r', encoding='utf-8') as f:
    text = f.read()

import re
lines = text.splitlines()
for i, line in enumerate(lines):
    if '11_Stok_Opname' in line or '44_Modal_Mutasi_Opname' in line or '32_Stok_Opname' in line:
        print(f"Line {i+1}: {line}")

