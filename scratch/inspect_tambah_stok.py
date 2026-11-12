with open(r'c:\xampp\htdocs\inventory_zencare\inventori\tambah_stok.php', 'r', encoding='utf-8') as f:
    text = f.read()

print('Length of tambah_stok.php:', len(text))
print('First 1000 chars:')
print(text[:1000])

