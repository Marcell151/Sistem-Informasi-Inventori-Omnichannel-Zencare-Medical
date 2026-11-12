with open(r'laporan_ta\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
m_sections = re.findall(r'<td colspan="6"[^>]*>(.*?)</td>', text, re.DOTALL)
print("Sections count:", len(m_sections))
for s in m_sections:
    print("-", re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', '', s)).strip())

