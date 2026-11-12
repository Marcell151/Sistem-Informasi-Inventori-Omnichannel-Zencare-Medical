with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i, line in enumerate(lines):
    if 'data-stok-sistem' in line:
        print(f"Line {i+1}: {line.strip()[:140]}")
    if 'function quickFillKasirDemo' in line:
        print(f"quickFillKasirDemo at line {i+1}")
        for j in range(i, min(i+40, len(lines))):
            print(f"  {j+1}: {lines[j]}", end='')

