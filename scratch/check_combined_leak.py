with open(r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

idx_leak = text.find('</div> <!-- Closes subbab_41_content -->')
print("Leak found in combined:", idx_leak != -1)
if idx_leak != -1:
    print(text[idx_leak-100:idx_leak+200])

