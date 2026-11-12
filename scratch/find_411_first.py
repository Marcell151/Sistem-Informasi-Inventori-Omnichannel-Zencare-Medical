with open("laporan_ta/BAB_IV_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

import re

# Search in the first 20,000 characters
print("First 2000 chars of file:")
print(text[:2000])

# Find where '4.1.1' appears in the first 60,000 characters
m = [m.start() for m in re.finditer(r'4\.1\.1', text[:60000])]
print("4.1.1 occurrences before 60k:", m)
for pos in m:
    print(f"At {pos}:")
    print(text[max(0, pos-100):pos+150])
    print("="*40)

