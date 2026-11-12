with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

idx = text.find('id="subbab_42"')
print("Around subbab_42:")
print(text[idx:idx+1500])
