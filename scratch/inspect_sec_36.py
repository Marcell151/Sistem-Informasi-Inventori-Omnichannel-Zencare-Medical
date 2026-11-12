with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('id="sec_3_6"')
print(text[pos:pos+3500])

