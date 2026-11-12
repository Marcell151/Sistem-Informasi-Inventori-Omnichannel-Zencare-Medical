with open('laporan_ta/DOKUMENTASI_TESTING_STOK_OPNAME.html', 'r', encoding='utf-8') as f:
    text = f.read()

forbidden = ['farmasi', 'apotek', 'hpp', 'akuntansi', 'audit', 'dosen', 'bab 4', 'sidang', 'sempurna', 'bimbingan']
found = {}
for w in forbidden:
    c = text.lower().count(w)
    if c > 0:
        found[w] = c

print('Forbidden words found in DOKUMENTASI_TESTING_STOK_OPNAME.html:', found)

