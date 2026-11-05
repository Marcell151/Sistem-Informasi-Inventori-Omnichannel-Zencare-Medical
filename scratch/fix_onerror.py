with open(r'admin\master_produk.php', 'r', encoding='utf-8') as f:
    text = f.read()

text = text.replace('onerror="this.src=\'../assets/img/no-image.png\'"', 'onerror="this.onerror=null; this.src=\'/inventory_zencare/assets/img/no-image.png\'"')

with open(r'admin\master_produk.php', 'w', encoding='utf-8') as f:
    f.write(text)

with open(r'scratch\clean_mp_utf8.html', 'r', encoding='utf-8') as f:
    text2 = f.read()

text2 = text2.replace('onerror="this.src=\'../assets/img/no-image.png\'"', 'onerror="this.onerror=null; this.src=\'/inventory_zencare/assets/img/no-image.png\'"')

with open(r'scratch\clean_mp_utf8.html', 'w', encoding='utf-8') as f:
    f.write(text2)

print('Updated onerror handlers successfully!')

