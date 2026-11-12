with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for j in range(3980, 4010):
    print(f"{j+1}: {lines[j]}", end='')

