# -*- coding: utf-8 -*-
with open('laporan_ta/DOKUMENTASI_SRS_LENGKAP_ZENCARE_MEDICAL.md', 'r', encoding='utf-8') as f:
    text = f.read()

idx = text.find('4.7')
if idx != -1:
    with open('scratch/srs_4_7.txt', 'w', encoding='utf-8') as out:
        out.write(text[idx:idx+3500])
    print("SRS 4.7 written to scratch/srs_4_7.txt")

