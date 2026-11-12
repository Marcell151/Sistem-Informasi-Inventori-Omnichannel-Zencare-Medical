with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos = text.find('3.5.17 Tabel sesi_opname')
pos_tb = text.find('<tbody>', pos)
pos_tbe = text.find('</tbody>', pos_tb)
print("BAB 3 sesi_opname tbody snippet:")
print(text[pos_tb:pos_tb+1200])

