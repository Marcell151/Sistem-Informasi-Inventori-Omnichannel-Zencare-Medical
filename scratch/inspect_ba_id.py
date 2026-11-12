with open('inventori/cetak_berita_acara_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('$idSesi')
print(text[pos-50:pos+300])

