with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    t1 = f.read()

with open(r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    t2 = f.read()

idx1 = t1.find('id="subbab_42"')
print("Bab 4 subbab_42 surrounding 500 chars before:\n", t1[idx1-300:idx1+100])

idx2 = t2.find('id="subbab_42"')
print("\nCombined subbab_42 surrounding 500 chars before:\n", t2[idx2-300:idx2+100])

