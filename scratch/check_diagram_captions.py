import re

with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

# find all fig captions around Gambar 3.10 to 3.17
for i in range(10, 18):
    pattern = rf'Gambar 3\.{i}[^<]*'
    matches = re.findall(pattern, text)
    print(f'Gambar 3.{i}:', matches)

