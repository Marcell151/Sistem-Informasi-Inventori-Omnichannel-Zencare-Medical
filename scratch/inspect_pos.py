with open('pos/pos.php', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

idx_main = text.find('<main')
if idx_main == -1:
    idx_main = text.find('grid')
print(text[idx_main:idx_main+2000].encode('ascii', errors='replace').decode('ascii'))
