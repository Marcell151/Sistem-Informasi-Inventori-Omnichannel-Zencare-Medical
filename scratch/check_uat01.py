with open(r'laporan_ta\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html', 'r', encoding='utf-8') as f:
    text = f.read()

idx = text.find('UAT-01')
print(text[idx-400:idx+600])

