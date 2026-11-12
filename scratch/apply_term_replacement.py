for file in ['laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html']:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. Mermaid diagram
    content = content.replace(
        'Kasir --> K3["Terminal Pencacahan Fisik Rak (Saat Sesi Freeze Aktif)"]',
        'Kasir --> K3["Input Fisik Stok Opname (Saat Sesi Freeze Aktif)"]'
    )
    content = content.replace(
        'K3_2["Pencacahan Fisik Tiga Kondisi (Baik, Rusak, Expired)"]',
        'K3_2["Input Fisik Tiga Kondisi (Baik, Rusak, Expired)"]'
    )
    content = content.replace(
        'K3_4["Simpan Hasil Hitung Fisik (Strict Blind Count)"]',
        'K3_4["Simpan Hasil Hitung Fisik (Blind Count)"]'
    )

    # 2. Text in Subbab 3.3.2
    content = content.replace(
        '<li><b>Terminal Pencacahan Fisik Rak (Strict Blind Count):</b> Antarmuka layar penuh khusus pencacahan fisik yang otomatis aktif menggantikan akses POS kasir ketika Superadmin membekukan sistem untuk audit stok opname.',
        '<li><b>Input Fisik Stok Opname (Blind Count):</b> Antarmuka layar penuh khusus input hitungan fisik yang otomatis aktif menggantikan akses POS kasir ketika Superadmin membekukan sistem untuk pemeriksaan stok opname.'
    )

    # 3. Text in Subbab 3.3.4
    content = content.replace(
        'akses POS otomatis dialihkan ke antarmuka <b>Terminal Pencacahan Fisik Rak (Strict Blind Count)</b> tanpa menampilkan angka stok sistem maupun jumlah pesanan yang direservasi.',
        'akses POS otomatis dialihkan ke antarmuka <b>Input Fisik Stok Opname (Blind Count)</b> tanpa menampilkan angka stok sistem maupun jumlah pesanan yang direservasi.'
    )

    with open(file, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Updated", file)

