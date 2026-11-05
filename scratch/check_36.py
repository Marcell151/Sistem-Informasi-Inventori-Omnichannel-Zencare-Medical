with open(r'c:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

idx = text.find('A. Login Staf')
print(text[idx:idx+1500])
