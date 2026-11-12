with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('onclick="isiOtomatisAngkaDemo()"')
print(text[pos-300:pos+200])

