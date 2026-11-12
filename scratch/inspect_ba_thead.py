with open('inventori/cetak_berita_acara_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('<thead>')
pos_end = text.find('<tbody>', pos)
print(text[pos:pos_end+500])

