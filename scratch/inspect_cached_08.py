with open(r'c:\xampp\htdocs\inventory_zencare\scratch\cache\08_Penerimaan_Barang.html', 'r', encoding='utf-8') as f:
    text = f.read()

print('Length of cached 08_Penerimaan_Barang.html:', len(text))
import re
# Find main containers, forms, tables
print('Forms:', len(re.findall(r'<form', text)))
print('Tables:', len(re.findall(r'<table', text)))
# Find title or main card headings
h_tags = re.findall(r'<h[1-4][^>]*>(.*?)</h[1-4]>', text)
for h in h_tags[:10]:
    print(' ', re.sub(r'<[^>]+>', '', h).strip())

