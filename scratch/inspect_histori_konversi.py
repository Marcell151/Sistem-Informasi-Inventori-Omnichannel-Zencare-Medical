with open(r'c:\xampp\htdocs\inventory_zencare\inventori\histori_konversi.php', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('<table')
if pos != -1:
    print('Table found in histori_konversi.php:')
    print(text[pos:pos+1200])
else:
    print('No table found in histori_konversi.php')

