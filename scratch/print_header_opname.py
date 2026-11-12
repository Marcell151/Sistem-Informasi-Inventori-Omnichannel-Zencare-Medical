with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i in range(min(80, len(lines))):
    print(f"{i+1}: {lines[i]}", end='')

