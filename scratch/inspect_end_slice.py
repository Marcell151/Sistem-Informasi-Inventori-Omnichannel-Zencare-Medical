with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    t1 = f.read()

with open(r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    t2 = f.read()

idx1 = t1.find('id="subbab_42"')
print("Bab 4 end slice:\n", t1[-800:])

idx2 = t2.find('id="subbab_42"')
print("\nCombined end slice:\n", t2[-800:])

