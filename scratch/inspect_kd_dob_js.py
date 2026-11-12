with open('lain/kamus_data_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos = text.find('"name": "detail_opname_batch"')
print("=== JS Data for detail_opname_batch ===")
print(text[pos-50:pos+2500])

