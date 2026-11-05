# -*- coding: utf-8 -*-
with open('lain/Gambar_3.16_Activity_Laporan_Manajerial.svg', 'r', encoding='utf-8') as f:
    text = f.read()

print("SVG length:", len(text))
for line in text.splitlines()[:60]:
    print(line)

