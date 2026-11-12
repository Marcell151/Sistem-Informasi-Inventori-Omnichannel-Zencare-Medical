with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos_start = text.find('BAB III')
pos_end = text.find('3.3.2')
print("BAB_III_ZENCARE_MEDICAL.html up to 3.3.2:")
print(text[pos_start:pos_start+3000])

