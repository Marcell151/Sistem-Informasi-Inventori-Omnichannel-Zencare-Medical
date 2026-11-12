with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    c = f.read()

pos34 = c.find('id="sec_3_4"')
pos35 = c.find('id="sec_3_5"')
print(c[pos34:pos35])

