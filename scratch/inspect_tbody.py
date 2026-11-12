with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

idx_start = text.find('<tbody>', text.find('Tabel 4.2'))
idx_end = text.find('</tbody>', idx_start)

print("First 1500 chars of tbody:\n", text[idx_start:idx_start+1500])

