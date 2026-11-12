with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos = text.find('3.3.3 Use Case')
if pos == -1: pos = text.find('3.3.3')
print("BAB_III_ZENCARE_MEDICAL.html Subbab 3.3.3 snippet:")
print(text[pos:pos+2500])

