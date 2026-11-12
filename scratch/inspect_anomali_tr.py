with open('inventori/cetak_berita_acara_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

pos = code.find('$isAnomali = ($sel !== 0')
print(code[pos:pos+1200])

