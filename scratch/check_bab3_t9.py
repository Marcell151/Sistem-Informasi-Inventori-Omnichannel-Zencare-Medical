with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos_t9 = text.find('Tabel 3.9')
print(text[pos_t9:pos_t9+3500])

