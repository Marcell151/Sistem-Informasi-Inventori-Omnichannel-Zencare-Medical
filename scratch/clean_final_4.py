targets = [
    'laporan_ta/BAB_III_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html'
]

reps = [
    ('kepatuhan audit', 'kepatuhan pengendalian'),
    ('saat audit fisik', 'saat pemeriksaan fisik'),
    ('audit kepatuhan', 'evaluasi kepatuhan'),
    ('sesi audit aktif', 'sesi opname aktif')
]

for fpath in targets:
    with open(fpath, 'r', encoding='utf-8') as f:
        text = f.read()
    for o, n in reps:
        text = text.replace(o, n)
    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(text)
    print(f"Cleaned remaining 4 in {fpath}")

