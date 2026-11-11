with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

text = text.replace('"Simpan Semua Hitungan Fisik"', '"Simpan Hasil Hitung"')

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(text)

print('Updated successfully!')

