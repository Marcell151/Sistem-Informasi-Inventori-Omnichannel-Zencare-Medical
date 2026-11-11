with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

# Fix logout
text = text.replace('/inventory_zencare/auth/logout.php', '/inventory_zencare/logout.php')

# Fix any remaining Simpan Semua Hitungan Fisik
text = text.replace('"Simpan Semua Hitungan Fisik"', '"Simpan Hasil Hitung"')
text = text.replace('Simpan Semua Hitungan Fisik', 'Simpan Hasil Hitung')

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(text)

print("Verified and updated logout + button text!")

