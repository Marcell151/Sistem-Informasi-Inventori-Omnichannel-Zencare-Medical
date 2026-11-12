with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

idx_42 = text.find('id="subbab_42"')
idx_422 = text.find('id="subbab_422"')
print("Between 42 and 422:\n", text[idx_42:idx_422])

