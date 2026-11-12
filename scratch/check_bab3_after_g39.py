with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos = text.find('Gambar 3.9')
print(text[pos:pos+4000])

