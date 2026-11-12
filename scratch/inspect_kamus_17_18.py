with open('lain/kamus_data_zencare.html', 'r', encoding='utf-8') as f:
    c = f.read()

pos17 = c.find('{"sub_no": "3.5.17"')
pos19 = c.find('{"sub_no": "3.5.19"')
print(c[pos17:pos19])

