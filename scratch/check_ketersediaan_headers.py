# -*- coding: utf-8 -*-
with open('laporan/ketersediaan_stok.php', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re
print("Has valuasi:", 'valuasi' in text.lower())
print("Has aset:", 'aset' in text.lower())
print("Has nilai persediaan:", 'nilai' in text.lower())
# Print table headers
ths = re.findall(r'<th[^>]*>([\s\S]*?)</th>', text)
print("Table headers:", [re.sub(r'<[^>]+>', '', t).strip() for t in ths[:10]])

