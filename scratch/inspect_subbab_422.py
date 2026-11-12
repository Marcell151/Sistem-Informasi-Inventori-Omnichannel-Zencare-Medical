with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

pos_start = text.find('id="subbab_422"')
pos_end = text.find('id="subbab_43"')

print(f"Start: {pos_start}, End: {pos_end}")
print(text[pos_start:pos_end])

