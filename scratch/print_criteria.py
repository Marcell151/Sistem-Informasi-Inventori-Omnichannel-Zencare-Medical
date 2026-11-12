with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('Tabel 4.10')
print(text[pos-1000:pos])
print("="*50)
print(text[pos+2000:pos+3500])

