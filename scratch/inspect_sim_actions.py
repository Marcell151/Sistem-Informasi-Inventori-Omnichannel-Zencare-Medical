with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

import re
matches = [m.start() for m in re.finditer(r'jalankan_simulasi_verifikasi|jalankan_simulasi_demo|isi_cepat_demo', code)]
for pos in matches:
    print(code[pos-50:pos+300])
    print('===')
