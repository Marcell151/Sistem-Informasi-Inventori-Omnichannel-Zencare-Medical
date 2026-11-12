replacements = [
    ("Audit Aktivitas Pengadaan, Mutasi, & Kartu Stok", "Pemeriksaan Aktivitas Pengadaan, Mutasi, & Kartu Stok"),
    ("Audit Aktivitas Pengadaan, Mutasi, &amp; Kartu Stok", "Pemeriksaan Aktivitas Pengadaan, Mutasi, &amp; Kartu Stok"),
    ("Audit Deviasi FEFO", "Rekapitulasi Deviasi FEFO"),
    ("Audit persediaan", "Pemeriksaan fisik stok persediaan"),
    ("audit persediaan", "pemeriksaan fisik stok persediaan"),
    ("Audit Stok Opname", "Pencacahan Stok Opname"),
    ("audit stok opname", "pencacahan stok opname"),
    ("Audit Fisik", "Pencacahan Fisik"),
    ("audit fisik", "pencacahan fisik"),
    ("audit berkala", "pemeriksaan berkala"),
    ("Audit Berkala", "Pemeriksaan Berkala"),
    ("audit inventori", "rekonsiliasi inventori"),
    ("Audit Inventori", "Rekonsiliasi Inventori"),
]

for file in ['lain/semua_sitemap_zencare.html', 'scratch/sitemap_lr.html']:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
    for old, new in replacements:
        content = content.replace(old, new)
    with open(file, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Updated", file)

