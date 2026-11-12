with open(r'c:\xampp\htdocs\inventory_zencare\config\layout.php', 'r', encoding='utf-8') as f:
    text = f.read()

for l in text.split('\n'):
    if 'penerimaan' in l.lower() or 'tambah_stok' in l.lower():
        print(l.strip())
