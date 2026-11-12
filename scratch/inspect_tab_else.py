with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

pos_verif = code.find('TAHAP 2: VERIFIKASI')
pos_tabs = code.find('TAB NAVIGASI VERIFIKASI KHUSUS SUPERADMIN', pos_verif)
pos_else = code.find('<?php else:', pos_tabs)
if pos_else != -1:
    print(code[pos_else:pos_else+600])
else:
    pos_endif = code.find('<?php endif;', pos_tabs)
    print("Endif found at:", pos_endif)
    print(code[pos_endif:pos_endif+600])
