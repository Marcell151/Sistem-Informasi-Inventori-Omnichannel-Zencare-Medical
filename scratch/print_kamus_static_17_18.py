with open('lain/kamus_data_zencare.html', 'r', encoding='utf-8') as f:
    c = f.read()

pos_sesi = c.find('id="sesi_opname"')
pos_sn = c.find('id="detail_opname_sn"')
print(c[pos_sesi:pos_sn])

