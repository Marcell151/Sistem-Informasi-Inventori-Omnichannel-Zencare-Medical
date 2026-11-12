with open('config/opname_helper.php', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('function cekSesiOpnameAktif')
print(text[pos:pos+500])

