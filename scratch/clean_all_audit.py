import re

files = [
    r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html',
    r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html'
]

replacements = [
    (r'\bKartu Stok Audit\b', 'Kartu Stok Persediaan'),
    (r'\bkartu stok audit\b', 'kartu stok persediaan'),
    (r'\bjejak audit kronologis\b', 'jejak riwayat kronologis'),
    (r'\bStok Opname dan Audit Fisik\b', 'Stok Opname dan Pemeriksaan Fisik'),
    (r'\bstok opname dan audit fisik\b', 'stok opname dan pemeriksaan fisik'),
    (r'\baudit fisik persediaan\b', 'pemeriksaan fisik persediaan'),
    (r'\baudit fisik\b', 'pencacahan fisik'),
    (r'\bAudit Fisik\b', 'Pencacahan Fisik'),
    (r'\bsesi audit\b', 'sesi stok opname'),
    (r'\bSesi Audit\b', 'Sesi Stok Opname'),
    (r'\baudit logistik masuk\b', 'pemantauan logistik masuk'),
    (r'\brekapitulasi audit pergerakan\b', 'rekapitulasi pergerakan'),
    (r'\bLaporan Audit Anomali Pengeluaran\b', 'Laporan Deteksi Anomali Pengeluaran'),
    (r'\blaporan audit anomali pengeluaran\b', 'laporan deteksi anomali pengeluaran'),
    (r'\bPanel audit kepatuhan\b', 'Panel pengawasan kepatuhan'),
    (r'\btabel audit yang memuat\b', 'tabel evaluasi yang memuat'),
    (r'\bAudit Rutin\b', 'Pemeriksaan Rutin'),
    (r'\baudit rutin\b', 'pemeriksaan rutin'),
    (r'\bLog audit mencatat\b', 'Log mutasi mencatat'),
    (r'\blog audit mencatat\b', 'log mutasi mencatat'),
    (r'\bselama audit berlangsung\b', 'selama pemeriksaan fisik berlangsung'),
    (r'\bmetode audit konvensional\b', 'metode pemeriksaan fisik konvensional'),
    (r'\brekapitulasi audit\b', 'rekapitulasi pemeriksaan fisik'),
    (r'\bRekapitulasi audit\b', 'Rekapitulasi pemeriksaan fisik'),
    (r'\baudit trail\b', 'rekam jejak riwayat mutasi'),
    (r'\bAudit Trail\b', 'Rekam Jejak Riwayat Mutasi'),
    (r'\baudit\b', 'pemeriksaan fisik'),
    (r'\bAudit\b', 'Pemeriksaan Fisik')
]

for fpath in files:
    with open(fpath, 'r', encoding='utf-8') as f:
        text = f.read()

    for pat, rep in replacements:
        text = re.sub(pat, rep, text)

    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(text)

    print(f"Cleaned {fpath}")

