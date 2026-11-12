with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('function isiOtomatisAngkaDemo()')
pos_end = text.find('</script>', pos)
print(text[pos:pos_end])

