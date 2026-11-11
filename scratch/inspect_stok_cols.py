with open('inventori/stok_opname.php', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

for i in range(1595, min(1645, len(lines))):
    print(f"{i+1}: {lines[i]}", end='')

