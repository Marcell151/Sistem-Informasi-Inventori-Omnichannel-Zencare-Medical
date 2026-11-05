with open('inventori/stok_opname.php', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

idx_html = 61343
print(text[idx_html+1200:idx_html+3200].encode('ascii', errors='replace').decode('ascii'))
