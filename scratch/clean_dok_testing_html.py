with open('laporan_ta/DOKUMENTASI_TESTING_STOK_OPNAME.html', 'r', encoding='utf-8') as f:
    text = f.read()

# Replace forbidden words
text = text.replace('Catatan: Audit Rutin', 'Catatan: Pemeriksaan Rutin')
text = text.replace('Log audit mencatat', 'Log rekonsiliasi mencatat')
text = text.replace('audit', 'pemeriksaan fisik')
text = text.replace('Audit', 'Pemeriksaan Fisik')
text = text.replace('dosen pembimbing', 'spesifikasi rancangan sistem')
text = text.replace('dosen', 'evaluator')
text = text.replace('Dosen', 'Evaluator')
text = text.replace('Bab 4.2', 'Pengujian Sistem')
text = text.replace('bab 4.2', 'pengujian sistem')
text = text.replace('Bab 4', 'Pengujian Sistem')
text = text.replace('bab 4', 'pengujian sistem')
text = text.replace('sidang', 'evaluasi operasional')
text = text.replace('Sidang', 'Evaluasi Operasional')
text = text.replace('sempurna', 'lengkap')
text = text.replace('Sempurna', 'Lengkap')

forbidden = ['farmasi', 'apotek', 'hpp', 'akuntansi', 'audit', 'dosen', 'bab 4', 'sidang', 'sempurna']
found = {}
for w in forbidden:
    c = text.lower().count(w)
    if c > 0:
        found[w] = c
print("DOKUMENTASI_TESTING_STOK_OPNAME.html forbidden after cleaning:", found)

with open('laporan_ta/DOKUMENTASI_TESTING_STOK_OPNAME.html', 'w', encoding='utf-8') as f:
    f.write(text)
print("Updated DOKUMENTASI_TESTING_STOK_OPNAME.html successfully.")

