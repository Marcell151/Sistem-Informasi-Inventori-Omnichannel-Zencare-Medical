with open('lain/kamus_data_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re
pos_script = text.find('<script>')
if pos_script != -1:
    script_content = text[pos_script:]
    pos_sesi_js = script_content.find('sesi_opname')
    if pos_sesi_js != -1:
        print("=== JS Data for sesi_opname ===")
        print(script_content[pos_sesi_js-50:pos_sesi_js+1500])

