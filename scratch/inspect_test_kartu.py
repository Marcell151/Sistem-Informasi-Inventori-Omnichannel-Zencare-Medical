with open('scratch/cache/test_kartu_stok.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
tables = re.findall(r'<table[\s\S]*?</table>', text)
print("Tables found:", len(tables))
trs = re.findall(r'<tr[\s\S]*?</tr>', text)
print("Rows found:", len(trs))
cards = re.findall(r'Amoxicillin', text)
print("Amoxicillin found:", len(cards))

