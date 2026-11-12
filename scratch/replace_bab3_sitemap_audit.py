for file in ['laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html']:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
    content = content.replace('Audit Aktivitas Pengadaan, Mutasi, & Kartu Stok', 'Pemeriksaan Aktivitas Pengadaan, Mutasi, & Kartu Stok')
    content = content.replace('Audit Aktivitas Pengadaan, Mutasi, &amp; Kartu Stok', 'Pemeriksaan Aktivitas Pengadaan, Mutasi, &amp; Kartu Stok')
    content = content.replace('Audit Deviasi FEFO', 'Rekapitulasi Deviasi FEFO')
    with open(file, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Updated", file)

