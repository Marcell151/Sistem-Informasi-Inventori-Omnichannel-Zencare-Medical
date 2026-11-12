with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = re.findall(r'UAT-\d+', text)
print("Total UAT-xx references:", len(matches))
print("Unique UAT-xx:", len(set(matches)))

# Count how many tables
tbl_matches = re.findall(r'Tabel 4\.\d+', text)
from collections import Counter
print("Table counts:", Counter(tbl_matches))

