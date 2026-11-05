# -*- coding: utf-8 -*-
with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

idx_f = text.find('F. Mutasi Stok Manual')
print(text[idx_f:idx_f + 5000])

