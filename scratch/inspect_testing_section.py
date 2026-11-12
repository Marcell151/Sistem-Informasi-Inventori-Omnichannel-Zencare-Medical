with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

pos_42 = text.find('4.2 PENGUJIAN SISTEM')
if pos_42 == -1:
    pos_42 = text.find('PENGUJIAN SISTEM')

print("Found at:", pos_42)
print(text[pos_42:pos_42+3500])

