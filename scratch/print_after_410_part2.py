with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('Tabel 4.10')
print(text[pos+4000:pos+6000])

