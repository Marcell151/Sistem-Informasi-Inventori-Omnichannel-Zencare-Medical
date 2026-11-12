import re

with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

for i in range(10, 18):
    pos = text.find(f'Gambar 3.{i}')
    pos2 = text.find(f'Gambar 3.{i}', pos + 20)
    print(f'=== Caption 3.{i} ===')
    print(text[pos2:pos2+100])

