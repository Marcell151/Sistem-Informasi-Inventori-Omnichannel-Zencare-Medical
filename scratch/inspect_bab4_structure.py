with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i in range(min(120, len(lines))):
    if any(k in lines[i] for k in ['4.1', '4.2', '4.3', 'BAB IV', 'Hasil']):
        print(f"{i+1}: {lines[i].strip()[:100]}")

