import os

forbidden = ['farmasi', 'apotek', 'hpp', 'akuntansi', 'audit', 'dosen', 'bab 4', 'sidang', 'sempurna']

target_files = [
    'inventori/stok_opname.php',
    'inventori/cetak_berita_acara_opname.php',
    'config/opname_helper.php',
    'config/layout.php',
    'Demo_Bersih.sql',
    'laporan_ta/DOKUMENTASI_TESTING_STOK_OPNAME.html'
]

for tf in target_files:
    if os.path.exists(tf):
        with open(tf, 'r', encoding='utf-8', errors='ignore') as f:
            c = f.read()
        f_found = {}
        for w in forbidden:
            cnt = c.lower().count(w)
            if cnt > 0:
                f_found[w] = cnt
        print(f"{tf}: {f_found if f_found else 'CLEAN (0)'}")

