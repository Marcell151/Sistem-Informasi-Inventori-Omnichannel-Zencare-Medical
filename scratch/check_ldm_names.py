with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    bab3 = f.read()

import re
pos = bab3.find('detail_opname_batch')
if pos != -1:
    print("In BAB 3 around detail_opname_batch:")
    print(bab3[pos-100:pos+1200])

with open('lain/logical_database_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    ldm = f.read()

pos_ldm = ldm.find('Table detail_opname_batch')
if pos_ldm != -1:
    print("\nIn logical_database_zencare.html:")
    print(ldm[pos_ldm:pos_ldm+1200])

