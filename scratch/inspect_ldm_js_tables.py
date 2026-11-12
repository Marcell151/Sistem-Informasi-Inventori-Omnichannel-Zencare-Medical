with open('lain/logical_database_zencare.html', 'r', encoding='utf-8') as f:
    c = f.read()

pos1 = c.find('name: "sesi_opname"')
pos2 = c.find('name: "detail_opname_sn"')
print(c[pos1-100:pos2+300])

