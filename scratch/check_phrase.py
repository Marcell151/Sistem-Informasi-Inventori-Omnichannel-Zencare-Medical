with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

count = text.count('Simpan Semua Hitungan Fisik')
print(f'Occurrences: {count}')
for i, line in enumerate(text.splitlines()):
    if 'Simpan Semua Hitungan Fisik' in line:
        print(f'{i+1}: {line}')

