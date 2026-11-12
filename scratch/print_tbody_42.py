with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('Tabel 4.2')
pos_tbody = text.find('<tbody>', pos)
pos_tbody_end = text.find('</tbody>', pos_tbody)
print(text[pos_tbody:pos_tbody_end+8])

