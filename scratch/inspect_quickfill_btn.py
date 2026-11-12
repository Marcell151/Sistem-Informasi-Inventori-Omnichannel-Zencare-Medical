with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i, line in enumerate(lines):
    if 'quickFillKasirDemo' in line:
        print(f"Line {i+1}: {line.strip()[:140]}")

