# -*- coding: utf-8 -*-
with open('laporan_ta/DOKUMENTASI_SRS_LENGKAP_ZENCARE_MEDICAL.md', 'r', encoding='utf-8') as f:
    text = f.read()

idx = text.find('## 5. MATRIKS')
if idx != -1:
    with open('scratch/srs_section5.txt', 'w', encoding='utf-8') as out:
        out.write(text[idx:idx+2500])
    print("Found ## 5.")

