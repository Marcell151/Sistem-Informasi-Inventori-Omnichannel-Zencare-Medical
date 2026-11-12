with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

pos = code.find('id="input_exp_')
print(code[pos-200:pos+400])

