# -*- coding: utf-8 -*-
with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

idx_g = text.find('G. Output (Laporan Manajerial &amp; Logistik)')
print(text[idx_g-50:idx_g+2500])

