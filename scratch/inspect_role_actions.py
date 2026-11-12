with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

import re
actions = ['mulai_sesi', 'selesaikan_sesi', 'batalkan_sesi', 'tutup_penghitungan']
for act in actions:
    pos = code.find(f"aksi === '{act}'")
    if pos != -1:
        print(f"=== {act} ===")
        print(code[pos-50:pos+350])
