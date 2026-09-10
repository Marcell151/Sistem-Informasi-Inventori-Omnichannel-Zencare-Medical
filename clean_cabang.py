import os, re

php_files = []
for root, dirs, files in os.walk('c:/xampp/htdocs/inventory_zencare'):
    for f in files:
        if f.endswith('.php'):
            php_files.append(os.path.join(root, f))

for file in php_files:
    with open(file, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
    
    orig_content = content
    
    content = content.replace("AND sc.id_cabang = ?", "")
    content = content.replace("AND sc.id_cabang=?", "")
    content = content.replace("sc.id_cabang = ?", "1=1")
    content = content.replace("sc.id_cabang=?", "1=1")
    content = content.replace("id_cabang = ?", "1=1")
    content = content.replace("id_cabang=?", "1=1")
    content = content.replace("AND id_cabang = ?", "")
    content = content.replace("AND id_cabang=?", "")
    content = content.replace("WHERE id_cabang = ?", "WHERE 1=1")
    content = content.replace("WHERE id_cabang=?", "WHERE 1=1")
    content = content.replace("stok_cabang", "stok_toko")
    
    content = re.sub(r'\s*\$id_cabang\s*=\s*CABANG_UTAMA_ID;.*?\n', '\n', content)
    content = re.sub(r'\s*\$id_cabang\s*=\s*intval\(\$_GET\[\'id_cabang\'\] \?\? 0\);.*?\n', '\n', content)
    content = content.replace("$id_cabang = 1;", "")
    content = content.replace("$_SESSION['id_cabang'] = CABANG_UTAMA_ID;", "")
    content = content.replace("$_SESSION['id_cabang'] = 1;", "")
    
    if orig_content != content:
        with open(file, 'w', encoding='utf-8') as f:
            f.write(content)
