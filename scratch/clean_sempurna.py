targets = [
    'laporan_ta/BAB_III_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html',
    'lain/wireframe_zencare.html'
]

for fpath in targets:
    with open(fpath, 'r', encoding='utf-8') as f:
        text = f.read()
    text = text.replace('(sempurna atau retur parsial)', '(diterima penuh atau retur parsial)')
    text = text.replace('sempurna atau retur parsial', 'diterima penuh atau retur parsial')
    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(text)
    print(f"Cleaned 'sempurna' in {fpath}")

