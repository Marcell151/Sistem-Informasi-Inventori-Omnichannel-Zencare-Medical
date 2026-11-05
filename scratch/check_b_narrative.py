# -*- coding: utf-8 -*-
with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

idx_b = text.find('B. Pesanan Daring (E-Commerce Checkout)')
print(text[idx_b:idx_b + 3500])

