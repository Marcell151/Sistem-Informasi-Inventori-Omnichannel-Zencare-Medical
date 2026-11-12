with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos_kasir = text.find('Sitemap Kasir')
print(text[pos_kasir:pos_kasir+3000])

