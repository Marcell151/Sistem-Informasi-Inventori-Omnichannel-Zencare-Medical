with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('Gambar 3.19')
print(text[pos-100:pos+300])

