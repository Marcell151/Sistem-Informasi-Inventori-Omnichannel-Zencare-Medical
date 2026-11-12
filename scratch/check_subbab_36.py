with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re

# Find Subbab 3.6
m_36 = re.search(r'(<h3[^>]*>.*?3\.6.*?</h3>)', text)
if m_36:
    print("Found Subbab 3.6 header:")
    print(m_36.group(0))
    pos = m_36.start()
    print("\n--- Subbab 3.6 snippet (1500 chars) ---")
    print(text[pos:pos+2000])
else:
    print("Subbab 3.6 header not found!")

