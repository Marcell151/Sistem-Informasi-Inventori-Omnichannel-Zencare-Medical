for f in ['laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'lain/logical_database_zencare.html']:
    with open(f, 'r', encoding='utf-8', errors='ignore') as fp:
        c = fp.read()
    print(f"=== {f} ===")
    print("Has Gambar_3.18 reference?:", "Gambar_3.18" in c)

