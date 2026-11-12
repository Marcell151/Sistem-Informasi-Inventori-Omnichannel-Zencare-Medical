with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

pos_bb = text.find('4.2.1')
pos_uat = text.find('4.2.2')
print("Black Box text length:", pos_uat - pos_bb)
print("Preview:")
print(text[pos_bb:pos_bb+1200])

