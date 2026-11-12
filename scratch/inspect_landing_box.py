with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('Status Sistem: Normal')
if pos != -1:
    print(text[pos-200:pos+800])

