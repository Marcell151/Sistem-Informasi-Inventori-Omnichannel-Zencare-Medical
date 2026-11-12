with open(r'laporan_ta\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
headers = re.findall(r'<td colspan="6"[^>]*>(.*?)</td>', text)
print("Headers count:", len(headers))
for h in headers:
    print("-", re.sub(r'<[^>]+>', '', h).strip())

