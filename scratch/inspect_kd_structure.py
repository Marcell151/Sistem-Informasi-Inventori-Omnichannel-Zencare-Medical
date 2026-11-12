with open('lain/kamus_data_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos = text.find('id="sesi_opname"')
print(text[pos:pos+2000])

