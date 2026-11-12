files = [
    'laporan_ta/BAB_IV_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html',
    'laporan_ta/LAPORAN_TA_ZENCARE_MEDICAL_FULL.html'
]

for fpath in files:
    with open(fpath, 'r', encoding='utf-8') as f:
        text = f.read()

    cnt = text.count('Sangat Layak')
    if cnt > 0:
        text = text.replace('Sangat Layak', 'Sesuai')
        with open(fpath, 'w', encoding='utf-8') as f:
            f.write(text)
        print(f"Replaced {cnt} occurrences of 'Sangat Layak' with 'Sesuai' in {fpath}")
    else:
        print(f"0 occurrences of 'Sangat Layak' in {fpath}")

