with open(r'c:\xampp\htdocs\inventory_zencare\inventori\tambah_stok.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
# Find headings or tabs or tables in tambah_stok.php
headings = re.findall(r'<h[1-4][^>]*>(.*?)</h[1-4]>', text)
print('Headings in tambah_stok.php:')
for h in headings:
    print(' ', re.sub(r'<[^>]+>', '', h).strip())

tabs = re.findall(r'tab[_-][a-zA-Z0-9_]+', text)
print('Tabs found:', set(tabs))

tables = re.findall(r'<table[^>]*>', text)
print('Total tables in tambah_stok.php:', len(tables))

