with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i, l in enumerate(lines):
    if 'logout' in l:
        print(f"{i+1}: {l.strip()}")

