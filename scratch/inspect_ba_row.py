with open('inventori/cetak_berita_acara_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

pos_tr = code.find('<tr class="')
if pos_tr == -1:
    pos_tr = code.find('foreach ($detailItems')
print(code[pos_tr:pos_tr+1500])

