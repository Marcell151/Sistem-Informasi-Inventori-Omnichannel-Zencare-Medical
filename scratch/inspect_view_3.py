with open('inventori/stok_opname.php', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

def print_range(start, end):
    for i in range(start-1, min(end, len(lines))):
        print(f"{i+1}: {lines[i]}", end='')

print("=== LINE 1251 TO 1500 ===")
print_range(1251, 1500)

