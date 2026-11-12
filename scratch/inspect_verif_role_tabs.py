with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

pos_verif = code.find('TAHAP 2: VERIFIKASI')
pos_tabs = code.find('status_eval=berselisih', pos_verif)
print(code[pos_tabs-200:pos_tabs+1200])
