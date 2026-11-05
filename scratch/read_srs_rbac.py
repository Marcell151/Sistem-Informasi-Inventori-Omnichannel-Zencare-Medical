# -*- coding: utf-8 -*-
with open('laporan_ta/DOKUMENTASI_SRS_LENGKAP_ZENCARE_MEDICAL.md', 'r', encoding='utf-8') as f:
    text = f.read()

idx = text.find('MATRIKS HAK AKSES PENGGUNA')
if idx != -1:
    with open('scratch/srs_rbac.txt', 'w', encoding='utf-8') as out:
        out.write(text[idx:idx+2500])
    print("RBAC written to scratch/srs_rbac.txt")
