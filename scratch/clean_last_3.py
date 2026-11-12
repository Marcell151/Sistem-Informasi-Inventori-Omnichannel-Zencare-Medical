targets = [
    'laporan_ta/BAB_III_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html'
]

reps = [
    ('dan modul audit persediaan', 'dan modul pemeriksaan persediaan'),
    ('guna mendukung audit ketersediaan', 'guna mendukung pemantauan ketersediaan'),
    ('dan menjaga integritas hasil audit.', 'dan menjaga integritas hasil stok opname.')
]

for fpath in targets:
    with open(fpath, 'r', encoding='utf-8') as f:
        text = f.read()
    for o, n in reps:
        text = text.replace(o, n)
    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(text)
    print(f"Cleaned last 3 in {fpath}")

