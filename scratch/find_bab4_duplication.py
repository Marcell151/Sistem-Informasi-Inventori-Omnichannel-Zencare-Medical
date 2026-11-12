with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re

# Search for BAB IV headings
headings = [m.start() for m in re.finditer(r'BAB IV', text)]
print('BAB IV occurrences at pos:', headings)

h2s = [m.start() for m in re.finditer(r'HASIL DAN PEMBAHASAN', text)]
print('HASIL DAN PEMBAHASAN occurrences at pos:', h2s)

papers = [m.start() for m in re.finditer(r'class="paper', text)]
print('class="paper" occurrences at pos:', papers)

for h in headings:
    print('Context of BAB IV at', h, ':')
    print(text[h-100:h+200])
    print('='*40)

