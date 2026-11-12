import os

for fname in ['konversi_uom.php', 'histori_konversi.php']:
    p = os.path.join(r'c:\xampp\htdocs\inventory_zencare\inventori', fname)
    with open(p, 'r', encoding='utf-8') as f:
        text = f.read()
    print(f'=== {fname} === (size: {len(text)})')
    print(text[:400])
    print()

