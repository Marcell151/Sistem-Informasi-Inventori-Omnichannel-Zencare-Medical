with open('lain/kamus_data_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos = text.find('id="detail_opname_batch"')
print(text[pos:pos+3000])

