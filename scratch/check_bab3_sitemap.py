with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos_sm = text.find('3.3.2 Sitemap')
if pos_sm == -1: pos_sm = text.find('3.3.2')
pos_sm_end = text.find('3.3.3')
print("BAB_III_ZENCARE_MEDICAL.html Subbab 3.3.2:")
print(text[pos_sm:pos_sm+4000])

