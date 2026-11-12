for file in ['laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html']:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. Update Kasir Stok Opname title & text in Subbab 3.3.3
    content = content.replace(
        '<li><b>Kelola Stok Opname (Pencacahan Fisik Rak):</b> Melaksanakan proses penghitungan dan pencacahan fisik persediaan di etalase dan rak penyimpanan (<i>blind count</i>) selama sesi stok opname aktif tanpa melihat angka kuantitas buku sistem guna menjamin integritas dan objektivitas data. Kasir mencatat kuantitas riil ke dalam tiga kolom kondisi fisik (Baik, Rusak, Expired).</li>',
        '<li><b>Kelola Stok Opname (Input Fisik Stok Opname / Blind Count):</b> Melaksanakan proses input hasil penghitungan fisik persediaan di etalase dan rak penyimpanan (<i>blind count</i>) selama sesi stok opname aktif tanpa melihat angka kuantitas buku sistem guna menjamin integritas dan objektivitas data persediaan. Kasir mencatat kuantitas riil ke dalam tiga kolom kondisi fisik (Baik, Rusak, Expired) dengan penguncian otomatis pada batch kedaluwarsa.</li>'
    )

    # 2. Update Superadmin text in Subbab 3.3.3 (clean forbidden term 'audit')
    content = content.replace(
        'Superadmin bertindak sebagai penentu kebijakan strategis usaha, pengawas keabsahan audit standar peredaran',
        'Superadmin bertindak sebagai penentu kebijakan strategis usaha, pengawas keabsahan standar tata kelola peredaran'
    )

    with open(file, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Updated Subbab 3.3.3 in", file)

