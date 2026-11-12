with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos = text.find('Gambar 3.29')
if pos != -1:
    print("=== Gambar 3.29 snippet in BAB III ===")
    print(text[pos-200:pos+1200])
else:
    print("Gambar 3.29 not found in BAB III!")

