with open('lain/kamus_data_zencare.html', 'r', encoding='utf-8') as f:
    c = f.read()

# Let's inspect sesi_opname in kamus_data_zencare.html
pos_sesi = c.find('{"sub_no": "3.5.17"')
if pos_sesi == -1:
    pos_sesi = c.find('"sesi_opname"')
print("Found sesi_opname at:", pos_sesi)
if pos_sesi != -1:
    print(c[pos_sesi-50:pos_sesi+500])

