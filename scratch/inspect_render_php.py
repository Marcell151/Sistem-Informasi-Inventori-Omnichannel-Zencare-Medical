with open(r'c:\xampp\htdocs\inventory_zencare\scratch\render_page.php', 'r', encoding='utf-8') as f:
    text = f.read()

print('Length of render_page.php:', len(text))
print(text[:800])

