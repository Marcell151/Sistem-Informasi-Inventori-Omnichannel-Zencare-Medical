with open('laporan_ta/LAPORAN_TA_ZENCARE_MEDICAL_FULL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re

m = re.findall(r'<td[^>]*>.*?BERHASIL.*?</td>', text, re.IGNORECASE)
print(f"Total matching 'BERHASIL' td tags: {len(m)}")
if m:
    print("Sample:", m[0])

# Also check for 'Sesuai'
m_sesuai = re.findall(r'<td[^>]*>.*?Sesuai.*?</td>', text, re.IGNORECASE)
print(f"Total matching 'Sesuai' td tags: {len(m_sesuai)}")
if m_sesuai:
    print("Sample sesuai:", m_sesuai[0])

