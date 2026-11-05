# -*- coding: utf-8 -*-
with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

idx_d = text.find('D. Kelola Pesanan Daring &amp; Pembatalan')
print(text[idx_d:idx_d + 3000])

