with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

pos_uat = text.find('4.2.2')
print("Found 4.2.2 at:", pos_uat)
print(text[pos_uat:pos_uat+4000])

