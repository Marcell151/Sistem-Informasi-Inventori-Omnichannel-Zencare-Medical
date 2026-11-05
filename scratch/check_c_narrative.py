# -*- coding: utf-8 -*-
with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

idx_c = text.find('C. Pesanan Luring (Transaksi Kasir / POS)')
print(text[idx_c:idx_c + 3000])

