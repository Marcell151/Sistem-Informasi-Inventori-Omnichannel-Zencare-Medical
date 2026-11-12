with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text_bab3 = f.read()

import re
print("Bab 3 references to 3.18:")
for line in text_bab3.splitlines():
    if "3.18" in line:
        print(line[:120])

