with open('inventori/konversi_uom.php', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

idx_html = text.find('<div')
print(text[idx_html:idx_html+2000].encode('ascii', errors='replace').decode('ascii'))

