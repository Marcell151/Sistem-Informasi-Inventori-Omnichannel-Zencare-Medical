import re

with open(r'C:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

pos_42 = text.find('4.2 PENGUJIAN SISTEM')
if pos_42 != -1:
    print('Found 4.2 PENGUJIAN SISTEM!')
    print(text[pos_42:pos_42+3000])
