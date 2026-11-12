with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    t1 = f.read()

with open(r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    t2 = f.read()

import re
s1 = re.search(r'<style>(.*?)</style>', t1, re.DOTALL)
s2 = re.search(r'<style>(.*?)</style>', t2, re.DOTALL)

print("CSS in BAB_IV has .paper:", '.paper' in s1.group(1))
print("CSS in Combined has .paper:", '.paper' in s2.group(1))

# Let's inspect .paper in both
for m in re.finditer(r'\.paper\s*\{[^}]*\}', s1.group(1)):
    print("BAB_IV .paper:", m.group(0))

for m in re.finditer(r'\.paper\s*\{[^}]*\}', s2.group(1)):
    print("Combined .paper:", m.group(0))

