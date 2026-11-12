with open(r'c:\xampp\htdocs\inventory_zencare\inventori\konversi_uom.php', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('id="qty_container"')
print(text[pos:pos+2000])
