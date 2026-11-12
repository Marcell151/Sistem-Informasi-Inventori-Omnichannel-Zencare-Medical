with open('lain/kamus_data_zencare.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
pos = text.find('Tabel penjualan')
pos_end = text.find('Tabel detail_penjualan', pos)
print(text[pos:pos+1500])

