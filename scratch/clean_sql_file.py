with open('Demo_Bersih.sql', 'r', encoding='utf-8') as f:
    text = f.read()

text = text.replace('PO Farmasi Kimia Farma', 'PO Distribusi Kimia Farma')
text = text.replace('obat farmasi resmi', 'obat resmi')
text = text.replace('Pelanggan Langsung (Apotek)', 'Pelanggan Langsung (Toko Fisik)')
text = text.replace('saat audit gudang', 'saat pemeriksaan fisik gudang')

# Verify forbidden words
forbidden = ['farmasi', 'apotek', 'hpp', 'akuntansi', 'audit', 'dosen', 'bab 4', 'sidang', 'sempurna']
found = {}
for w in forbidden:
    c = text.lower().count(w)
    if c > 0:
        found[w] = c

print("Demo_Bersih.sql forbidden found after replacement:", found)

with open('Demo_Bersih.sql', 'w', encoding='utf-8') as f:
    f.write(text)
print("Updated Demo_Bersih.sql successfully.")

