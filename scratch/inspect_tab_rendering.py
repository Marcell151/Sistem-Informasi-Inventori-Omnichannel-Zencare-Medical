with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

pos = code.find('flex items-center gap-1.5">\n                        <a href="?komoditas=')
if pos == -1:
    pos = code.find('status_eval=belum_hitung')
print(code[pos-200:pos+1200])
