with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

print("--- Around Line 2655 ---")
for j in range(2650, 2665):
    if j < len(lines):
        print(f"{j+1}: {lines[j]}", end='')

print("\n--- Around Line 4002 ---")
for j in range(3990, 4040):
    if j < len(lines):
        print(f"{j+1}: {lines[j]}", end='')

