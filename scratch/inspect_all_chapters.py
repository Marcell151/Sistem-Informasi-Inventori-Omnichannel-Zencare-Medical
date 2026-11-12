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
    
    pos_bottom = text.find('<!-- Bottom Notification')
    print(f"{fpath:40} Paper pos: {pos_paper:6} | Bottom pos: {pos_bottom:6} | Len: {len(text):6}")

