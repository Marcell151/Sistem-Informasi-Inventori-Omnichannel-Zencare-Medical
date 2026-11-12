import re

files = [
    'laporan_ta/BAB_I_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_II_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_III_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_IV_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_V_ZENCARE_MEDICAL.html'
]

for fpath in files:
    with open(fpath, 'r', encoding='utf-8') as f:
        text = f.read()

    pos_paper = text.find('class="paper')
    if pos_paper == -1:
        pos_paper = text.find("class='paper")
    
    pos_open = text.find('>', pos_paper) + 1
    pos_bottom = text.find('<!-- Bottom Notification')
    pos_close = text.rfind('</div>', 0, pos_bottom)

    inner = text[pos_open:pos_close].strip()
    print(f"File: {fpath:40} Inner len: {len(inner):6} | Start: {inner[:40].replace(chr(10), ' ')}... | End: ...{inner[-40:].replace(chr(10), ' ')}")

