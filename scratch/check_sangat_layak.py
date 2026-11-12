with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re

m_sangat = re.findall(r'Sangat Layak', text)
m_layak = re.findall(r'\bLayak\b', text)
print(f"Total 'Sangat Layak': {len(m_sangat)}")
print(f"Total 'Layak': {len(m_layak)}")

