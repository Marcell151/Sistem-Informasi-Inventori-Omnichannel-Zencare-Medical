replacements = [
    ("Audit Opname", "Stok Opname"),
    ("audit opname", "stok opname"),
    ("audit fisik", "pemeriksaan fisik"),
    ("Audit Fisik", "Pemeriksaan Fisik"),
    ("audit stok opname", "pemeriksaan stok opname"),
    ("Audit Stok Opname", "Pemeriksaan Stok Opname"),
    ("audit persediaan", "pemantauan persediaan"),
    ("Audit Persediaan", "Pemantauan Persediaan"),
    ("audit kepatuhan", "pemantauan kepatuhan"),
    ("Audit Kepatuhan", "Pemantauan Kepatuhan"),
    ("pelaksanaan audit", "pelaksanaan stok opname"),
    ("Pelaksanaan audit", "Pelaksanaan stok opname"),
    ("sesi audit", "sesi opname"),
    ("Sesi audit", "Sesi opname"),
    ("kebutuhan audit", "kebutuhan evaluasi"),
    ("log audit", "catatan riwayat"),
    ("Log audit", "Catatan riwayat"),
]

for file in ['lain/logical_database_zencare.html', 'lain/kamus_data_zencare.html']:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
    for old, new in replacements:
        content = content.replace(old, new)
    with open(file, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Cleaned audit in", file)

