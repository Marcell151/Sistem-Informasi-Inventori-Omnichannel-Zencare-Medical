with open('config/layout.php', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

for i, line in enumerate(lines):
    if 'stok_opname' in line or 'kasir' in line.lower():
        print(f"{i+1}: {line}", end='')

