with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('3.4 Logical Database Model')
if pos == -1:
    pos = text.find('3.4')

print(text[pos:pos+2000])

