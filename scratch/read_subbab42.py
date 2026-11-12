with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos42 = text.find('id="subbab_42"')
print(text[pos42+14000:])
