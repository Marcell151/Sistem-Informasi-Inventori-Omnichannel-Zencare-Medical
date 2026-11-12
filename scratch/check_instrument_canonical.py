with open(r'laporan_ta\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = re.findall(r'UAT-\d+', text)
print("Total UAT-xx in INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html:", len(matches))
print("Unique:", len(set(matches)))

