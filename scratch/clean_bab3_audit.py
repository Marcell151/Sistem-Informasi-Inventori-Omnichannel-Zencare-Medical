targets = [
    'laporan_ta/BAB_III_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html'
]

replacements = [
    ('kepatuhan audit persediaan medis', 'kepatuhan pengendalian persediaan medis'),
    ('rekam audit pengadaan barang', 'rekam jejak pengadaan barang'),
    ('audit pergerakan kuantitas fisik', 'pemantauan pergerakan kuantitas fisik'),
    ('variabel akuntansi keuangan', 'variabel pembukuan keuangan'),
    ('mengaudit kepatuhan pengambilan', 'mengevaluasi kepatuhan pengambilan'),
    ('variabel akuntansi finansial', 'variabel pembukuan finansial'),
    ('merekam jejak audit (audit trail)', 'merekam riwayat mutasi persediaan'),
    ('jejak audit (audit trail)', 'rekam jejak mutasi'),
    ('saat audit.', 'saat stok opname.'),
    ('saat audit', 'saat stok opname'),
    ('kegiatan audit fisik persediaan', 'kegiatan pemeriksaan fisik persediaan'),
    ('pelaporan audit logistik masuk', 'pelaporan pemantauan logistik masuk'),
    ('rekapitulasi audit pergerakan', 'rekapitulasi pemantauan pergerakan'),
    ('Laporan Audit Anomali Pengeluaran FEFO', 'Laporan Anomali Pengeluaran FEFO'),
    ('Halaman Laporan Audit Anomali', 'Halaman Laporan Anomali'),
    ('Panel audit kepatuhan protokol', 'Panel pemantauan kepatuhan protokol'),
    ('Halaman Kartu Stok Audit', 'Halaman Kartu Stok Persediaan'),
    ('Kartu Stok Audit', 'Kartu Stok Persediaan'),
    ('Stok Opname dan Audit Fisik', 'Stok Opname Persediaan')
]

for fpath in targets:
    with open(fpath, 'r', encoding='utf-8') as f:
        text = f.read()
    for old, new in replacements:
        text = text.replace(old, new)
    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(text)
    print(f"Sanitized terms in {fpath}")

