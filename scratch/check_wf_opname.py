with open('scratch/wf_stok_opname.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

print("scratch/wf_stok_opname.html length:", len(text))
print("\nFirst 1000 chars:")
print(text[:1000])

import re
# check for audit, terminal pencacahan, freeze, kasir, admin, superadmin, etc.
for term in ['audit', 'terminal', 'pencacahan', 'tahap', 'verifikasi', 'kasir', 'fisik_baik', 'rusak', 'expired', 'blind']:
    matches = len(re.findall(r'\b' + term, text, re.IGNORECASE))
    print(f"Term '{term}': {matches} matches")

