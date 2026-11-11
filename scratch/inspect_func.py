with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i, l in enumerate(lines):
    if 'function isiOtomatisAngkaDemo' in l:
        start = i
        break

print(''.join(lines[start:start+75]))

