with open(r'c:\xampp\htdocs\inventory_zencare\laporan\penerimaan_barang.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
tables = [m.start() for m in re.finditer(r'<table', text)]
for t in tables[:3]:
    print('--- TABLE IN LAPORAN AT', t, '---')
    print(text[t:t+400])
    print()

