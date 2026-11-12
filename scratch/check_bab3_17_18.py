with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re

pos = text.find('3.5.17 Tabel sesi_opname')
pos_end = text.find('3.5.18 Tabel detail_opname_batch')
print("=== BAB 3 - 3.5.17 Tabel sesi_opname ===")
print(text[pos:pos_end])

pos_end2 = text.find('3.5.19 Tabel detail_opname_sn')
print("\n=== BAB 3 - 3.5.18 Tabel detail_opname_batch ===")
print(text[pos_end:pos_end2])

