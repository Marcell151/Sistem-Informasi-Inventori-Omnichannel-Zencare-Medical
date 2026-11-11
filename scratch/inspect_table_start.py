with open('inventori/stok_opname.php', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

for i in range(1415, 1465):
    print(f"{i+1}: {lines[i]}", end='')

