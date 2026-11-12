import re

with open(r'C:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

pattern = r'<td[^>]*>\s*(\d+)\s*</td>\s*<td[^>]*>\s*([a-z0-9_]+)'
matches = re.findall(pattern, text)
print(f'Total field row matches: {len(matches)}')
cols = sorted(list(set(m[1] for m in matches)))
print(f'Distinct column names ({len(cols)}):')
for c in cols:
    print(' ', c)

