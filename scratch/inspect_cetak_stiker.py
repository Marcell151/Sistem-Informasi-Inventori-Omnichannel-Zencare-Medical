with open(r'c:\xampp\htdocs\inventory_zencare\inventori\cetak_batch_stiker.php', 'r', encoding='utf-8') as f:
    text = f.read()

print('Length of cetak_batch_stiker.php:', len(text))
print(text[:800])

