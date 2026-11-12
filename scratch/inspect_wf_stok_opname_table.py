with open('scratch/wf_stok_opname.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos = text.find('<!-- Tabs Nav & Filter -->')
print(text[pos:pos+4000])

