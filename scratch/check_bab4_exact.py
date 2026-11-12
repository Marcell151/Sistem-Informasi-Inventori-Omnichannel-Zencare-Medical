import re
with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

m = re.findall(r'\bbab\s+4\b', text, re.IGNORECASE)
print('Exact matches for standalone "Bab 4":', len(m))

m_all = re.findall(r'bab\s*4', text, re.IGNORECASE)
print('All matches for "bab 4":', len(m_all))
for item in re.finditer(r'bab\s*4', text, re.IGNORECASE):
    s = item.start()
    print("   ", text[max(0, s-20):min(len(text), s+30)].replace('\n', ' '))

