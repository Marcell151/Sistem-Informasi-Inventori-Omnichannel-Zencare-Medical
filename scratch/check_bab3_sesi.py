with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re
pos = text.find('sesi_opname')
while pos != -1:
    print("--- Found sesi_opname at", pos, "---")
    print(text[max(0, pos-100):min(len(text), pos+600)])
    pos = text.find('sesi_opname', pos+600)

