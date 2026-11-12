import re

with open('scratch/latest_user_prompt.txt', 'r', encoding='utf-8') as f:
    text = f.read()

# Replace audit in 3.5.19 and 3.5.20
text_clean = text.replace(
    'Nomor seri fisik alat kesehatan yang dipindai saat audit.',
    'Nomor seri fisik alat kesehatan yang dipindai saat pemeriksaan fisik persediaan.'
)
text_clean = text_clean.replace(
    'Fungsi: Entitas baru yang merekam riwayat koreksi atau perhitungan ulang fisik selama sesi audit aktif berlangsung beserta alasan perubahannya guna mencegah manipulasi angka persediaan dan menjaga integritas hasil audit.',
    'Fungsi: Entitas yang merekam riwayat koreksi atau perhitungan ulang fisik selama sesi pemeriksaan stok opname aktif berlangsung beserta alasan perubahannya guna mencegah manipulasi angka persediaan dan menjaga integritas hasil rekonsiliasi fisik.'
)

forbidden = ['farmasi', 'apotek', 'hpp', 'akuntansi', 'audit', 'dosen', 'bab 4', 'sidang', 'sempurna']
found = {}
for w in forbidden:
    c = text_clean.lower().count(w)
    if c > 0:
        found[w] = c
print("Cleaned text forbidden found:", found)

with open('scratch/cleaned_kamus_data.txt', 'w', encoding='utf-8') as f:
    f.write(text_clean)
print("Saved to scratch/cleaned_kamus_data.txt")

