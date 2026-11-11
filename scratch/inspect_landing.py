with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

print('=== Lines 35-65 ===')
print(''.join(lines[35:65]))

print('=== Lines 1500-1540 ===')
print(''.join(lines[1500:1540]))

