with open('inventori/stok_opname.php', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos = text.find('id="modal_mutasi"')
print(text[pos:pos+2500])
