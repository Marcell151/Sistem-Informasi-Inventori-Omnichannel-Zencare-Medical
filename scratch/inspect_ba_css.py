with open('inventori/cetak_berita_acara_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

pos_style = code.find('<style>')
pos_style_end = code.find('</style>', pos_style)
print(code[pos_style:pos_style_end+8])

