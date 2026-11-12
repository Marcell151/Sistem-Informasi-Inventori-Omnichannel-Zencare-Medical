with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    t1 = f.read()

import re
# Find all divs with max-w-4xl
m = re.findall(r'<div[^>]*max-w-4xl[^>]*>', t1)
print("max-w-4xl divs in Bab 4:")
for d in m:
    print(" -", d)

# Find the main container around BAB IV heading
idx_head = t1.find('BAB IV')
print("Surrounding 300 chars of BAB IV heading:\n", t1[idx_head-200:idx_head+200])

