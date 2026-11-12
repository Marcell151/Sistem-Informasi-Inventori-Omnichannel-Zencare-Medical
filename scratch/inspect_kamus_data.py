import re

with open(r'C:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text_iii_iv = f.read()

# Let's inspect a table row in Kamus Data
pos = text_iii_iv.find('3.5 Kamus Data')
snippet = text_iii_iv[pos:pos+3000]
print(snippet[:2000])

