with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i, l in enumerate(lines):
    if '<h2' in l or '<h3' in l or '<h4' in l:
        print(f"{i+1}: {l.strip()[:100]}")

